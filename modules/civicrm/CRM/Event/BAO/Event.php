<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 2.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2009                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007.                                       |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2009
 * $Id$
 *
 */

require_once 'CRM/Event/DAO/Event.php';

class CRM_Event_BAO_Event extends CRM_Event_DAO_Event 
{

    /**
     * class constructor
     */
    function __construct( ) 
    {
        parent::__construct( );
    }
    
    /**
     * Takes a bunch of params that are needed to match certain criteria and
     * retrieves the relevant objects. Typically the valid params are only
     * contact_id. We'll tweak this function to be more full featured over a period
     * of time. This is the inverse function of create. It also stores all the retrieved
     * values in the default array
     *
     * @param array $params   (reference ) an assoc array of name/value pairs
     * @param array $defaults (reference ) an assoc array to hold the flattened values
     *
     * @return object CRM_Event_BAO_ManageEvent object
     * @access public
     * @static
     */
    static function retrieve( &$params, &$defaults ) 
    {
        $event  = new CRM_Event_DAO_Event( );
        $event->copyValues( $params );
        if ( $event->find( true ) ) {
            CRM_Core_DAO::storeValues( $event, $defaults );
            return $event;
        }
        return null;
    }

    /**
     * update the is_active flag in the db
     *
     * @param int      $id        id of the database record
     * @param boolean  $is_active value we want to set the is_active field
     *
     * @return Object             DAO object on sucess, null otherwise
     * @static
     */
    static function setIsActive( $id, $is_active ) 
    {
        return CRM_Core_DAO::setFieldValue( 'CRM_Event_DAO_Event', $id, 'is_active', $is_active );
    }
    
    /**
     * function to add the event
     *
     * @param array $params reference array contains the values submitted by the form
     * 
     * @access public
     * @static 
     * @return object
     */
    static function add( &$params )
    {
        require_once 'CRM/Utils/Hook.php';
        
        if ( CRM_Utils_Array::value( 'id', $params ) ) {
            CRM_Utils_Hook::pre( 'edit', 'Event', $params['id'], $params );
        } else {
            CRM_Utils_Hook::pre( 'create', 'Event', null, $params ); 
        }
        
        $event =& new CRM_Event_DAO_Event( );
        
        $event->copyValues( $params );
        $result = $event->save( );
        
        if ( CRM_Utils_Array::value( 'id', $params ) ) {
            CRM_Utils_Hook::post( 'edit', 'Event', $event->id, $event );
        } else {
            CRM_Utils_Hook::post( 'create', 'Event', $event->id, $event );
        }
        
        return $result;
    }
    
    /**
     * function to create the event
     *
     * @param array $params reference array contains the values submitted by the form
     * 
     * @access public
     * @static 
     * 
     */
    public static function create( &$params ) 
    {
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        $event = self::add( $params );
        
        if ( is_a( $event, 'CRM_Core_Error') ) {
            CRM_Core_DAO::transaction( 'ROLLBACK' );
            return $event;
        }
        
        $session   = & CRM_Core_Session::singleton();
        $contactId = $session->get('userID');
        if ( !$contactId ) {
            $contactId = $params['contact_id'];
        }
        
        // Log the information on successful add/edit of Event
        require_once 'CRM/Core/BAO/Log.php';
        $logParams = array(
                           'entity_table'  => 'civicrm_event',
                           'entity_id'     => $event->id,
                           'modified_id'   => $contactId,
                           'modified_date' => date('Ymd')
                           );
        
        CRM_Core_BAO_Log::add( $logParams );
        
        if ( CRM_Utils_Array::value( 'custom', $params ) &&
             is_array( $params['custom'] ) ) {
            require_once 'CRM/Core/BAO/CustomValueTable.php';
            CRM_Core_BAO_CustomValueTable::store( $params['custom'], 'civicrm_event', $event->id );
        }
        
        $transaction->commit( );
        
        return $event;
    }
    
    /**
     * Function to delete the event
     *
     * @param int $id  event id
     *
     * @access public
     * @static
     *
     */
    static function del( $id )
    { 
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $extends   = array('event');
        $groupTree = CRM_Core_BAO_CustomGroup::getGroupDetail( null, null, $extends );
        foreach( $groupTree as $values ) {
            $query = "DELETE FROM " . $values['table_name'] . " WHERE entity_id = " . $id ; 
            
            $params = array( 1 => array( $values['table_name'], 'string'),
                             2 => array( $id, 'integer') );
            
            CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        }
        
        $dependencies = array(
                              'CRM_Core_DAO_OptionGroup'   => array( 'name'        => 'civicrm_event.amount.'.$id ),
                              'CRM_Core_DAO_UFJoin'        => array(
                                                                    'entity_id'    => $id,
                                                                    'entity_table' => 'civicrm_event' ),
                              );
        require_once 'CRM/Core/BAO/OptionGroup.php';
        foreach ( $dependencies as $daoName => $values ) {
            require_once (str_replace( '_', DIRECTORY_SEPARATOR, $daoName ) . ".php");
            eval('$dao =& new ' . $daoName . '( );');
            if ( $daoName == 'CRM_Core_DAO_OptionGroup' ) {
                $dao->name = $values['name'];
                $dao->find( );
                while ( $dao->fetch( ) ) {
                    CRM_Core_BAO_OptionGroup::del( $dao->id );
                }
            } else { 
                foreach ( $values as $fieldName => $fieldValue ) {
                    $dao->$fieldName = $fieldValue;
                }
                
                $dao->find();
                
                while ( $dao->fetch() ) {
                    $dao->delete();
                }
            }
        }
        require_once 'CRM/Core/OptionGroup.php';
        CRM_Core_OptionGroup::deleteAssoc ("civicrm_event.amount.{$id}.discount.%", "LIKE");
        require_once 'CRM/Event/DAO/Event.php';
        $event     = & new CRM_Event_DAO_Event( );
        $event->id = $id; 
        
        if ( $event->find( true ) ) {
            $locBlockId = $event->loc_block_id;
            $result     = $event->delete( );

            if ( ! is_null( $locBlockId ) ) {
                self::deleteEventLocBlock( $locBlockId, $id );
            }
            return $result;
        }
        
        return null;
    }
    
    /**
     * Function to delete the location block associated with an event, 
     * if not being used by any other event.
     *
     * @param int $loc_block_id    location block id to be deleted
     * @param int $eventid         event id with which loc block is associated
     *
     * @access public
     * @static
     *
     */
    static function deleteEventLocBlock( $locBlockId, $eventId = null )
    {
        $query = "SELECT count(ce.id) FROM civicrm_event ce WHERE ce.loc_block_id = $locBlockId";

        if ( $eventId ) {
            $query .= " AND ce.id != $eventId;";
        }

        $locCount = CRM_Core_DAO::singleValueQuery( $query );

        if ( $locCount == 0 ) {
            require_once 'CRM/Core/BAO/Location.php';
            CRM_Core_BAO_Location::deleteLocBlock( $locBlockId );
        }
    }

    /**
     * Function to get current/future Events 
     *
     * @param $all boolean true if events all are required else returns current and future events
     *
     * @static
     */
    static function getEvents( $all = false, $id = false) 
    {
        $query = "SELECT `id`, `title`, `start_date` FROM `civicrm_event`";
        
        if ( !$all ) {
            $endDate = date( 'YmdHis' );
            $query .= " WHERE `end_date` >= {$endDate} OR end_date IS NULL";
        }
        if ( $id ) {
            $query .= " WHERE `id` = {$id}";
        }

        $query .= " ORDER BY title asc";
        $events = array( );
        
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        while ( $dao->fetch( ) ) {
            $events[$dao->id] = $dao->title . ' - '.CRM_Utils_Date::customFormat($dao->start_date);
        }
        
        return $events;
    }
    
    /**
     * Function to get events Summary
     *
     * @static
     * @return array Array of event summary values
     */
    static function getEventSummary( $admin = false )
    {
        $eventSummary = array( );
        
        $query = "SELECT count(id) as total_events
                  FROM   civicrm_event 
                  WHERE  civicrm_event.is_active=1";
        
        $dao =& CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray );
        
        if ( $dao->fetch( ) ) {
            $eventSummary['total_events'] = $dao->total_events;
        }
        
        if ( empty( $eventSummary ) ||
             $dao->total_events == 0 ) {
            return $eventSummary;
        }

        // Get the Id of Option Group for Event Types
        require_once 'CRM/Core/DAO/OptionGroup.php';
        $optionGroupDAO = new CRM_Core_DAO_OptionGroup();
        $optionGroupDAO->name = 'event_type';
        $optionGroupId = null;
        if ($optionGroupDAO->find(true) ) {
            $optionGroupId = $optionGroupDAO->id;
        }
        
        $query = "
SELECT     civicrm_event.id as id, civicrm_event.title as event_title, civicrm_event.is_public as is_public,
           civicrm_event.max_participants as max_participants, civicrm_event.start_date as start_date,
           civicrm_event.end_date as end_date, civicrm_event.is_map as is_map, civicrm_option_value.label as event_type,
           civicrm_event.summary as summary
FROM       civicrm_event
LEFT JOIN  civicrm_option_value ON (
           civicrm_event.event_type_id = civicrm_option_value.value AND
           civicrm_option_value.option_group_id = %1 )
WHERE      civicrm_event.is_active = 1
GROUP BY   civicrm_event.id
ORDER BY   civicrm_event.end_date DESC
LIMIT      0, 10
";

        $eventParticipant = array( );
        $params = array( 1 => array( $optionGroupId, 'Integer' ) );

        $dao =& CRM_Core_DAO::executeQuery( $query, $params );

        $eventParticipant['participants'] = self::getParticipantCount( );
        $eventParticipant['pending']      = self::getParticipantCount( true );

        $properties = array( 'eventTitle'      => 'event_title',      'isPublic'     => 'is_public', 
                             'maxParticipants' => 'max_participants', 'startDate'    => 'start_date', 
                             'endDate'         => 'end_date',         'eventType'    => 'event_type', 
                             'isMap'           => 'is_map',           'participants' => 'participants',
                             'pending'         => 'pending',
                             );
        
        while ( $dao->fetch( ) ) {
            require_once 'CRM/Core/Config.php';
            $config = CRM_Core_Config::singleton();
            
            foreach ( $properties as $property => $name ) {
                $set = null;
                
                if (( $name == 'start_date' ) || 
                    ( $name == 'end_date' ) ) {
                    $eventSummary['events'][$dao->id][$property] = 
                        CRM_Utils_Date::customFormat($dao->$name,
                                                     null,
                                                     array( 'd' ) );
                } else if ( $name == 'participants' || $name == 'pending' ) {
                    if ( CRM_Utils_Array::value( $dao->id, $eventParticipant[$name] ) ) {
                        $eventSummary['events'][$dao->id][$property] = $eventParticipant[$name][$dao->id] ? $eventParticipant[$name][$dao->id] : 0;
                    } else {
                        $eventSummary['events'][$dao->id][$property] = 0;
                    }
                    
                    if ( $name == 'participants' && 
                         CRM_Utils_Array::value( $dao->id, $eventParticipant['participants'] ) ) { 
                        // pass the status true to get status with filter = 1
                        $set = CRM_Utils_System::url( 'civicrm/event/search',"reset=1&force=1&event=$dao->id&status=true" );
                    } else if ( $name == 'pending' && CRM_Utils_Array::value( $dao->id, $eventParticipant['pending'] ) ) {
                        $set = CRM_Utils_System::url( 'civicrm/event/search',"reset=1&force=1&event=$dao->id&status=false" );
                    }
                    
                    $eventSummary['events'][$dao->id][$name.'_url'] = $set;
                } else if ( $name == 'is_public' ) {
                    if ( $dao->$name ) {
                        $set = 'Yes';
                    } else {
                        $set = 'No';
                    }
                    
                    $eventSummary['events'][$dao->id][$property] = $set;
                } else if ( $name == 'is_map' ) {
                    if ( $dao->$name && $config->mapAPIKey ) {
                        $params = array();
                        $values = array();
                        $ids    = array();
                        
                        $params = array( 'entity_id' => $dao->id ,'entity_table' => 'civicrm_event');

                        require_once 'CRM/Core/BAO/Location.php';
                        CRM_Core_BAO_Location::getValues($params, $values, true );
                        
                        if ( is_numeric( CRM_Utils_Array::value('geo_code_1',$values['location'][1]['address']) ) ||
                             ( $config->mapGeoCoding &&
                               $values['location'][1]['address']['city'] && 
                               $values['location'][1]['address']['state_province_id']
                             ) ) {
                            $set = CRM_Utils_System::url( 'civicrm/contact/map',"reset=1&eid={$dao->id}" );
                        }
                    }
                    
                    $eventSummary['events'][$dao->id][$property] = $set;
                    if ( $admin ) {
                        $eventSummary['events'][$dao->id]['configure'] =
                            CRM_Utils_System::url( "civicrm/admin/event", "action=update&id=$dao->id&reset=1" );
                    }
                } else {
                    $eventSummary['events'][$dao->id][$property] = $dao->$name;
                }
            }
        }
        require_once 'CRM/Event/PseudoConstant.php';

        $statusTypes         = CRM_Event_PseudoConstant::participantStatus( null, "filter = 1" );
        $statusTypesPending  = CRM_Event_PseudoConstant::participantStatus( null, "filter = 0" );
        
        $eventSummary['statusDisplay'] = implode( '/', array_values( $statusTypes ) );
        $eventSummary['statusDisplayPending'] = implode( '/', array_values( $statusTypesPending ) );
        return $eventSummary;
    }

    /**
     * Function to get participant count
     *
     * @param  int   $status  we pass status only for pending
     *
     * @access public
     * @return array array with count of participants for each status
     *
     */
    function getParticipantCount( $status = null ) 
    {
        if ( !$status ) {
            require_once 'CRM/Event/PseudoConstant.php';
            $statusTypes  = CRM_Event_PseudoConstant::participantStatus( null, "filter = 1" ); 
            $status = implode( ',', array_keys( $statusTypes ) );
            if ( !$status ) {
                $status = 0;
            }
        } else {
            require_once 'CRM/Event/PseudoConstant.php';
            $statusTypes  = CRM_Event_PseudoConstant::participantStatus( null, "filter = 0" ); 
            $status = implode( ',', array_keys( $statusTypes ) );
            if ( !$status ) {
                $status = 0;
            }
        } 

        
        $query = "
SELECT civicrm_event.id AS id, count( civicrm_participant.id ) AS participant
FROM civicrm_event, civicrm_participant 
WHERE civicrm_event.id = civicrm_participant.event_id
  AND civicrm_participant.is_test = 0 
  AND civicrm_participant.status_id IN ( {$status} )
  AND civicrm_event.is_active = 1
GROUP BY civicrm_event.id
ORDER BY civicrm_event.end_date DESC
LIMIT 0 , 10
";
        $participant = array( );
        $daoStatus =& CRM_Core_DAO::executeQuery( $query,
                                                  CRM_Core_DAO::$_nullArray );
        while ( $daoStatus->fetch( ) ) {
            $participant[$daoStatus->id] = $daoStatus->participant;
        }
        return $participant;
    }

    /**
     * function to get the information to map a event
     *
     * @param  array  $ids    the list of ids for which we want map info
     *
     * @return null|string     title of the event
     * @static
     * @access public
     */
        
    static function &getMapInfo(&$id ) 
    {

        $sql = "
SELECT 
   civicrm_event.id AS event_id, 
   civicrm_event.title AS display_name, 
   civicrm_address.street_address AS street_address, 
   civicrm_address.city AS city, 
   civicrm_address.postal_code AS postal_code, 
   civicrm_address.postal_code_suffix AS postal_code_suffix, 
   civicrm_address.geo_code_1 AS latitude, 
   civicrm_address.geo_code_2 AS longitude, 
   civicrm_state_province.abbreviation AS state, 
   civicrm_country.name AS country, 
   civicrm_location_type.name AS location_type
FROM 
   civicrm_event
   LEFT JOIN civicrm_loc_block ON ( civicrm_event.loc_block_id = civicrm_loc_block.id )
   LEFT JOIN civicrm_address ON ( civicrm_loc_block.address_id = civicrm_address.id )
   LEFT JOIN civicrm_state_province ON ( civicrm_address.state_province_id = civicrm_state_province.id )
   LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
   LEFT JOIN civicrm_location_type ON ( civicrm_location_type.id = civicrm_address.location_type_id )
WHERE civicrm_address.geo_code_1 IS NOT NULL 
  AND civicrm_address.geo_code_2 IS NOT NULL 
  AND civicrm_event.id = " . CRM_Utils_Type::escape( $id, 'Integer' );
       
        $dao =& new CRM_Core_DAO( );
        $dao->query( $sql );

        $locations = array( );

        $config =& CRM_Core_Config::singleton( );

        while ( $dao->fetch( ) ) {
       
            $location = array( );
            $location['displayName'] = addslashes( $dao->display_name );
            $location['lat'        ] = $dao->latitude;
            $location['lng'        ] = $dao->longitude;
            $address = '';

            CRM_Utils_String::append( $address, '<br />',
                                      array( $dao->street_address,
                                             $dao->city ) );
            CRM_Utils_String::append( $address, ', ',
                                      array(   $dao->state, $dao->postal_code ) );
            CRM_Utils_String::append( $address, '<br /> ',
                                      array( $dao->country ) );
            $location['address'      ] = addslashes( $address );
            $location['url'          ] = CRM_Utils_System::url( 'civicrm/event/register', 'reset=1&id=' . $dao->event_id );
            $location['location_type'] = $dao->location_type;
            $eventImage = '<img src="' . $config->resourceBase . 'i/contact_org.gif" alt="Organization " height="20" width="15" />';
            $location['image'] = $eventImage;
            $location['displayAddress'] = str_replace( '<br />', ', ', $address );
            $locations[] = $location;
        }
        return $locations;
    }

    /**
     * function to get the complete information of an event
     *
     * @param  date    $start    the start date for the event
     * @param  integer $type     the type id for the event 
     *
     * @return  array  $all      array of all the events that are searched
     * @static
     * @access public
     */      
    static function &getCompleteInfo( $start = null, $type = null, $eventId = null ) 
    {
       
        if ( $start ) {
            // get events with start_date >= requested start
            $condition =  CRM_Utils_Type::escape( $start, 'Date' );
        } else {
            // get events with start date >= today
            $condition =  date("Ymd");
        }
        if ( $type ) {
            $condition = $condition . " AND civicrm_event.event_type_id = " . CRM_Utils_Type::escape( $type, 'Integer' ); 

        }

        // Get the Id of Option Group for Event Types
        require_once 'CRM/Core/DAO/OptionGroup.php';
        $optionGroupDAO = new CRM_Core_DAO_OptionGroup();
        $optionGroupDAO->name = 'event_type';
        $optionGroupId = null;
        if ($optionGroupDAO->find(true) ) {
            $optionGroupId = $optionGroupDAO->id;
        }
        
        $query = "
SELECT
  civicrm_event.id as event_id, 
  civicrm_email.email as email, 
  civicrm_event.title as title, 
  civicrm_event.summary as summary, 
  civicrm_event.start_date as start, 
  civicrm_event.end_date as end, 
  civicrm_event.description as description, 
  civicrm_event.is_show_location as is_show_location, 
  civicrm_event.is_online_registration as is_online_registration,
  civicrm_event.registration_link_text as registration_link_text,
  civicrm_event.registration_start_date as registration_start_date,
  civicrm_event.registration_end_date as registration_end_date,
  civicrm_option_value.label as event_type, 
  civicrm_address.name as address_name, 
  civicrm_address.street_address as street_address, 
  civicrm_address.supplemental_address_1 as supplemental_address_1, 
  civicrm_address.supplemental_address_2 as supplemental_address_2, 
  civicrm_address.city as city, 
  civicrm_address.postal_code as postal_code, 
  civicrm_address.postal_code_suffix as postal_code_suffix, 
  civicrm_state_province.abbreviation as state, 
  civicrm_country.name AS country
FROM civicrm_event
LEFT JOIN civicrm_loc_block ON civicrm_event.loc_block_id = civicrm_loc_block.id
LEFT JOIN civicrm_address ON civicrm_loc_block.address_id = civicrm_address.id
LEFT JOIN civicrm_state_province ON civicrm_address.state_province_id = civicrm_state_province.id
LEFT JOIN civicrm_country ON civicrm_address.country_id = civicrm_country.id
LEFT JOIN civicrm_email ON civicrm_loc_block.email_id = civicrm_email.id
LEFT JOIN civicrm_option_value ON (
                                    civicrm_event.event_type_id = civicrm_option_value.value AND
                                    civicrm_option_value.option_group_id = %1 )
WHERE civicrm_event.is_active = 1 
      AND civicrm_event.is_public = 1 
      AND civicrm_event.start_date >= {$condition}"; 
    
        if(isset( $eventId )) {
            $query .= " AND civicrm_event.id =$eventId ";
        }
        $query .=" ORDER BY   civicrm_event.start_date ASC";


        $params = array( 1 => array( $optionGroupId, 'Integer' ) );
        $dao =& CRM_Core_DAO::executeQuery( $query, $params );
        $all = array( );
        $config =& CRM_Core_Config::singleton( );
        
        $baseURL = parse_url( $config->userFrameworkBaseURL );
        $url = "@".$baseURL['host'];
        if ( CRM_Utils_Array::value( 'path', $baseURL ) ) {
            $url .= substr( $baseURL['path'], 0, -1 );
        }

        require_once 'CRM/Utils/String.php';
        while ( $dao->fetch( ) ) {
        
            $info                     = array( );
            $info['uid'          ]    = 
            $info['uid'          ]    = "CiviCRM_EventID_{$dao->event_id}_" . md5( $config->userFrameworkBaseURL ) . $url;

            $info['title'        ]    = $dao->title;
            $info['event_id'     ]    = $dao->event_id;
            $info['summary'      ]    = $dao->summary;
            $info['description'  ]    = $dao->description;
            $info['start_date'   ]    = $dao->start;
            $info['end_date'     ]    = $dao->end;
            $info['contact_email']    = $dao->email;
            $info['event_type'   ]    = $dao->event_type;
            $info['is_show_location'] = $dao->is_show_location;
            $info['is_online_registration'] = $dao->is_online_registration;
            $info['registration_link_text'] = $dao->registration_link_text;
            $info['registration_start_date'] = $dao->registration_start_date;
            $info['registration_end_date'] = $dao->registration_end_date;
  
            $address = '';

            $addrFields = array(
                                'address_name'           => $dao->address_name,
                                'street_address'         => $dao->street_address,
                                'supplemental_address_1' => $dao->supplemental_address_1,
                                'supplemental_address_2' => $dao->supplemental_address_2,
                                'city'                   => $dao->city,
                                'state_province'         => $dao->state,
                                'postal_code'            => $dao->postal_code,
                                'postal_code_suffix'     => $dao->postal_code_suffix,
                                'country'                => $dao->country,
                                'county'                 => null
                                );           
            
            require_once 'CRM/Utils/Address.php';
            CRM_Utils_String::append( $address, ', ',
                                      CRM_Utils_Address::format($addrFields) );
            $info['location'     ] = $address;
            $info['url'          ] = CRM_Utils_System::url( 'civicrm/event/info', 'reset=1&id=' . $dao->event_id, true, null, false );
           
            $all[] = $info;
        }
        
        return $all;
    }

    /**
     * This function is to make a copy of a Event, including
     * all the fields in the event Wizard
     *
     * @param int $id the event id to copy
     *
     * @return void
     * @access public
     */
    static function copy( $id )
    {
        $defaults = $eventValues = array( );
        
        //get the require event values.
        $eventParams = array( 'id' => $id );
        $returnProperties = array( 'loc_block_id', 'is_show_location', 'default_fee_id', 'default_discount_id' );
        
        CRM_Core_DAO::commonRetrieve( 'CRM_Event_DAO_Event', $eventParams, $eventValues, $returnProperties );
        
        // since the location is sharable, lets use the same loc_block_id.
        $locBlockId     = CRM_Utils_Array::value( 'loc_block_id', $eventValues );
        
        $fieldsToPrefix = array( 'title' => ts( 'Copy of ' ) );
        
        if ( !CRM_Utils_Array::value( 'is_show_location', $eventValues ) ) {
            $fieldsToPrefix['is_show_location'] = 0;
        }
        
        $copyEvent      =& CRM_Core_DAO::copyGeneric( 'CRM_Event_DAO_Event', 
                                                      array( 'id' => $id ), 
                                                      array( 'loc_block_id' => 
                                                             ( $locBlockId ) ? $locBlockId : null ), 
                                                      $fieldsToPrefix );
        
        $copyPriceSet   =& CRM_Core_DAO::copyGeneric( 'CRM_Core_DAO_PriceSetEntity', 
                                                      array( 'entity_id'    => $id,
                                                             'entity_table' => 'civicrm_event'),
                                                      array( 'entity_id'    => $copyEvent->id ) );
        
        $copyUF         =& CRM_Core_DAO::copyGeneric( 'CRM_Core_DAO_UFJoin',
                                                      array( 'entity_id'    => $id,
                                                             'entity_table' => 'civicrm_event'),
                                                      array( 'entity_id'    => $copyEvent->id ) );
        
        $copyTellFriend =& CRM_Core_DAO::copyGeneric( 'CRM_Friend_DAO_Friend', 
                                                      array( 'entity_id'    => $id,
                                                             'entity_table' => 'civicrm_event'),
                                                      array( 'entity_id'    => $copyEvent->id ) );
        
        require_once "CRM/Core/BAO/OptionGroup.php";
        //copy option Group and values
        $copyEvent->default_fee_id = CRM_Core_BAO_OptionGroup::copyValue('event', 
                                                                         $id, 
                                                                         $copyEvent->id,
                                                                         CRM_Utils_Array::value( 'default_fee_id', $eventValues )
                                                                         );
        
        //copy discounted fee levels
        require_once 'CRM/Core/BAO/Discount.php';
        $discount = CRM_Core_BAO_Discount::getOptionGroup( $id, 'civicrm_event' );
        
        if ( !empty ( $discount ) ) {
            foreach ( $discount as $discountOptionGroup ) {
                $name = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_OptionGroup',
                                                     $discountOptionGroup );
                $length         = substr_compare($name, "civicrm_event.amount.". $id, 0);
                $discountSuffix = substr($name, $length * (-1));
                
                $copyEvent->default_discount_id = 
                    CRM_Core_BAO_OptionGroup::copyValue('event', 
                                                        $id, 
                                                        $copyEvent->id, 
                                                        CRM_Utils_Array::value( 'default_discount_id', $eventValues ),
                                                        $discountSuffix );
            }
        }
        
        //copy custom data
        require_once 'CRM/Core/BAO/CustomGroup.php';
        $extends   = array('event');
        $groupTree = CRM_Core_BAO_CustomGroup::getGroupDetail( null, null, $extends );
        if ( $groupTree ) {
            foreach ( $groupTree as $groupID => $group ) {
                $table[$groupTree[$groupID]['table_name']] = array( 'entity_id');
                foreach ( $group['fields'] as $fieldID => $field ) {
                    $table[$groupTree[$groupID]['table_name']][] = $groupTree[$groupID]['fields'][$fieldID]['column_name'];
                }
            }
            
            foreach ( $table as $tableName => $tableColumns ) {
                $insert = 'INSERT INTO ' . $tableName. ' (' .implode(', ',$tableColumns). ') '; 
                $tableColumns[0] = $copyEvent->id;
                $select = 'SELECT ' . implode(', ',$tableColumns); 
                $from = ' FROM '  . $tableName;
                $where = " WHERE {$tableName}.entity_id = {$id}"  ;
                $query = $insert . $select . $from . $where;
                $dao = CRM_Core_DAO::executeQuery( $query, CRM_Core_DAO::$_nullArray ); 
            }
        }   
        $copyEvent->save( );

        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::copy( 'Event', $copyEvent );

        return $copyEvent;
    }
    
    /**
     * This is sometimes called in a loop (during event search)
     * hence we cache the values to prevent repeated calls to the db
     */
    static function isMonetary( $id ) {
        static $isMonetary = array( );
        if ( ! array_key_exists( $id, $isMonetary ) ) {
            $isMonetary[$id] = CRM_Core_DAO::getFieldValue( 'CRM_Event_DAO_Event',
                                                            $id,
                                                            'is_monetary' );
        }
        return $isMonetary[$id];
    }

    /**
     * This is sometimes called in a loop (during event search)
     * hence we cache the values to prevent repeated calls to the db
     */
    static function usesPriceSet( $id ) {
        require_once 'CRM/Core/BAO/PriceSet.php';
        static $usesPriceSet = array( );
        if ( ! array_key_exists( $id, $usesPriceSet ) ) {
            $usesPriceSet[$id] = CRM_Core_BAO_PriceSet::getFor( 'civicrm_event', $id );
        }
        return $usesPriceSet[$id];
    }

    /**
     * Process that send e-mails
     *
     * @return void
     * @access public
     */
    static function sendMail( $contactID, &$values, $participantId, $isTest = false, $returnMessageText = false ) 
    {
        require_once 'CRM/Core/BAO/UFGroup.php';
        //this condition is added, since same contact can have
        //multiple event registrations..       
        $params = array( array( 'participant_id', '=', $participantId, 0, 0 ) );
        $gIds = array(
                      'custom_pre_id' => $values['custom_pre_id'],
                      'custom_post_id'=> $values['custom_post_id']
                      );
        
        if ( ! $returnMessageText ) {
            //send notification email if field values are set (CRM-1941)
            foreach ( $gIds as $gId ) {
                if ( $gId ) {
                    $email = CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_UFGroup', $gId, 'notify' );
                    if ( $email ) {
                        $val = CRM_Core_BAO_UFGroup::checkFieldsEmptyValues( $gId, $contactID, $params );         
                        CRM_Core_BAO_UFGroup::commonSendMail( $contactID, $val );
                    }
                }
            }
        }
        
        if ( $values['event']['is_email_confirm'] ) {
            $template =& CRM_Core_Smarty::singleton( );
            require_once 'CRM/Contact/BAO/Contact/Location.php';
            // if pay later or for additional participant than we should use primary email address    
            if ( CRM_Utils_Array::value( 'is_pay_later', $values['params'] ) ||
                 CRM_Utils_Array::value( 'additionalParticipant', $values['params'] ) )  {
                list( $displayName, $email ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $contactID );
            } else {
                // get the billing location type
                $locationTypes =& CRM_Core_PseudoConstant::locationType( );
                $bltID = array_search( 'Billing',  $locationTypes );
                list( $displayName, $email ) = CRM_Contact_BAO_Contact_Location::getEmailDetails( $contactID, false, $bltID );
            }
    
            //send email only when email is present
            if ( isset( $email ) ) {
                self::buildCustomDisplay( $values['custom_pre_id'] , 'customPre' , $contactID, $template, $participantId, $isTest );
                self::buildCustomDisplay( $values['custom_post_id'], 'customPost', $contactID, $template, $participantId, $isTest );
                
                // set confirm_text and contact email address for display in the template here
                $template->assign( 'email', $email );
                $template->assign( 'confirm_email_text', CRM_Utils_Array::value( 'confirm_email_text', $values['event'] ) );
                
                $isShowLocation = CRM_Utils_Array::value('is_show_location',$values['event']);
                $template->assign( 'isShowLocation', $isShowLocation );
                
                $subject = trim( $template->fetch( 'CRM/Event/Form/Registration/ReceiptSubject.tpl' ) );
                $message = $template->fetch( 'CRM/Event/Form/Registration/ReceiptMessage.tpl' );
                if (function_exists('mb_encode_mimeheader')) { // try to fix CRM-4631 - ideally should be moved to CRM_Utils_Mail::send()
                    $values['event']['confirm_from_name'] = mb_encode_mimeheader($values['event']['confirm_from_name'], 'UTF-8', 'Q');
                }
                $receiptFrom = '"' . $values['event']['confirm_from_name'] . '" <' . $values['event']['confirm_from_email'] . '>';
                
                if ( $returnMessageText ) {
                    return array( 'subject' => $subject,
                                  'body'    => $message,
                                  'to'      => $displayName );
                }
                
                require_once 'CRM/Utils/Mail.php';
                CRM_Utils_Mail::send( $receiptFrom,
                                      $displayName,
                                      $email,
                                      $subject,
                                      $message,
                                      CRM_Utils_Array::value( 'cc_confirm', $values['event'] ),
                                      CRM_Utils_Array::value( 'bcc_confirm', $values['event'] )
                                      );
            }
        }
    }
    
    /**  
     * Function to add the custom fields OR array of participant's
     * profile info
     *  
     * @return None  
     * @access public  
     */ 
    function buildCustomDisplay( $gid, $name, $cid, &$template, $participantId, $isTest, $isCustomProfile = false ) 
    {  
        if ( $gid ) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            if ( CRM_Core_BAO_UFGroup::filterUFGroups($gid, $cid) ){
                $values = array( );
                $fields = CRM_Core_BAO_UFGroup::getFields( $gid, false, CRM_Core_Action::VIEW );

                //this condition is added, since same contact can have multiple event registrations..
                $params = array( array( 'participant_id', '=', $participantId, 0, 0 ) );
                
                //add participant id
                $fields['participant_id'] = array ( 'name' => 'participant_id',
                                                    'title'=> 'Participant Id');
                //check whether its a text drive
                if ( $isTest ) {
                    $params[] = array( 'participant_test', '=', 1, 0, 0 );
                }
                
                $groupTitle = null;
                foreach( $fields as $k => $v  ) {
                    if ( ! $groupTitle ) {
                        $groupTitle = $v["groupTitle"];
                    }
                    // suppress all file fields from display
                    if ( CRM_Utils_Array::value( 'data_type', $v, '' ) == 'File' ) {
                        unset( $fields[$k] );
                    }
                }

                if ( $groupTitle ) {
                    $template->assign( $name."_grouptitle", $groupTitle );
                }


                CRM_Core_BAO_UFGroup::getValues( $cid, $fields, $values , false, $params );

                if ( isset($values[$fields['participant_status_id']['title']]) &&
                     is_numeric( $values[$fields['participant_status_id']['title']] ) ) {
                    $status = array( );
                    $status = CRM_Event_PseudoConstant::participantStatus( );
                    $values[$fields['participant_status_id']['title']] = $status[$values[$fields['participant_status_id']['title']]];
                }
                
                if ( isset( $values[$fields['participant_role_id']['title']] ) &&
                     is_numeric( $values[$fields['participant_role_id']['title']] ) ) {
                    $roles = array( );
                    $roles = CRM_Event_PseudoConstant::participantRole( );
                    $values[$fields['participant_role_id']['title']] = $roles[$values[$fields['participant_role_id']['title']]];
                }

                if ( isset($values[$fields['participant_register_date']['title']]) ) {
                    $values[$fields['participant_register_date']['title']] = 
                        CRM_Utils_Date::customFormat($values[$fields['participant_register_date']['title']]);
                }
                
                //handle fee_level for price set
                if ( isset( $values[$fields['participant_fee_level']['title']] ) ) {
                    $feeLevel = explode( CRM_Core_BAO_CustomOption::VALUE_SEPERATOR, 
                                         $values[$fields['participant_fee_level']['title']] );
                    foreach ( $feeLevel as $key => $val ) {
                        if ( ! $val ) {
                            unset( $feeLevel[$key] );
                        }
                    }
                    $values[$fields['participant_fee_level']['title']] = implode( ",", $feeLevel );
                }
                
                unset( $values[$fields['participant_id']['title']] );

                //return if we only require array of participant's info.
                if ( $isCustomProfile ) {
                    if ( count($values) ) {
                        return $values;
                    } else {
                        return null;
                    }
                } 

                if ( count( $values ) ) {
                    $template->assign( $name, $values );
                }
            }
        }
    }
    
    /**  
     * Function to build the array for display the profile fields
     *  
     * @param array $params key value. 
     * @param int $gid profile Id
     * @param array $groupTitle Profile Group Title.
     * @param array $values formatted array of key value
     *
     * @return None  
     * @access public  
     */ 
    function displayProfile( &$params, $gid, &$groupTitle, &$values ) 
    {   
        if ( $gid ) {
            require_once 'CRM/Core/BAO/UFGroup.php';
            require_once 'CRM/Profile/Form.php';
            $session =& CRM_Core_Session::singleton( );
            $contactID = $session->get( 'userID' );
            if ( $contactID ) {
                if ( CRM_Core_BAO_UFGroup::filterUFGroups($gid, $contactID ) ) {
                    $fields = CRM_Core_BAO_UFGroup::getFields( $gid, false, CRM_Core_Action::VIEW );
                }
            } else {
                $fields = CRM_Core_BAO_UFGroup::getFields( $gid, false, CRM_Core_Action::ADD ); 
            }
            
            if ( is_array( $fields ) ) {
                // unset any email-* fields since we already collect it, CRM-2888
                foreach ( array_keys( $fields ) as $fieldName ) {
                    if ( substr( $fieldName, 0, 6 ) == 'email-' ) {
                        unset( $fields[$fieldName] );
                    }
                }
            }

            foreach ( $fields as $v  ) {
                if ( CRM_Utils_Array::value( 'groupTitle', $v ) ) {
                    $groupTitle['groupTitle'] = $v["groupTitle"];
                    break;
                }
            }
            
            $config =& CRM_Core_Config::singleton( );
            require_once 'CRM/Core/PseudoConstant.php'; 
            $locationTypes = $imProviders = array( );
            $locationTypes = CRM_Core_PseudoConstant::locationType( );
            $imProviders   = CRM_Core_PseudoConstant::IMProvider( );
            //start of code to set the default values
            foreach ($fields as $name => $field ) { 
                $index   = $field['title'];
                $customFieldName = null;
                if ( $name === 'organization_name' ) {
                    $values[$index] = $params[$name];
                }
                
                if ( 'state_province' == substr( $name, 0, 14 ) ) {
                    $values[$index] = CRM_Core_PseudoConstant::stateProvince( $params[$name] );
                } else if ( 'country' == substr( $name, 0, 7 ) ) {
                    $values[$index] = CRM_Core_PseudoConstant::country( $params[$name] );
                } else if ( 'county' == substr( $name, 0, 6 ) ) {
                    $values[$index] = $params[$name];
                } else if ( 'gender' == substr( $name, 0, 6 ) ) {
                    $gender =  CRM_Core_PseudoConstant::gender( );
                    $values[$index] = $gender[$params[$name]];
                } else if ( 'individual_prefix' == substr( $name, 0, 17 ) ) {
                    $prefix =  CRM_Core_PseudoConstant::individualPrefix( );
                    $values[$index] = $prefix[$params[$name]];
                } else if ( 'individual_suffix' == substr( $name, 0, 17 ) ) {
                    $suffix = CRM_Core_PseudoConstant::individualSuffix( );
                    $values[$index] = $suffix[$params[$name]];
                } else if ( 'greeting_type' == substr( $name, 0, 13 ) ) {
                    $greeting = CRM_Core_PseudoConstant::greeting( );
                    $values[$index] = $greeting[$params[$name]];
                } else if ( $name === 'preferred_communication_method' ) {
                    $communicationFields = CRM_Core_PseudoConstant::pcm();
                    $pref = array();
                    $compref = array();
                    $pref = $params[$name];
                    if( is_array($pref) ) {
                        foreach($pref as $k => $v ) {
                            if ( $v ) {
                                $compref[] = $communicationFields[$k];
                            }
                        }
                    }
                    $values[$index] = implode( ",", $compref);
                } else if ( $name == 'group' ) {
                    require_once 'CRM/Contact/BAO/GroupContact.php';
                    $groups = CRM_Contact_BAO_GroupContact::getGroupList( );
                    $title = array( ); 
                    foreach ( $params[$name] as $gId => $dontCare ) {
                        if ( $dontCare ) {
                            $title[] = $groups[$gId];
                        }
                    }
                    $values[$index] = implode( ', ', $title );
                } else if ( $name == 'tag' ) {
                    require_once 'CRM/Core/BAO/EntityTag.php';
                    $entityTags = $params[$name];
                    $allTags    =& CRM_Core_PseudoConstant::tag();
                    $title = array( );
                    if ( is_array($entityTags) ) {
                        foreach ( $entityTags as $tagId => $dontCare ) { 
                            $title[] = $allTags[$tagId];
                        }
                    }
                    $values[$index] = implode( ', ', $title );
                } else if ( 'participant_role_id' == $name ) {
                    $roles = CRM_Event_PseudoConstant::participantRole( );
                    $values[$index] = $roles[$params[$name]];
                } else if ( 'participant_status_id' == $name ) {
                    $status = CRM_Event_PseudoConstant::participantStatus( );
                    $values[$index] = $status[$params[$name]];
                } else if ( strpos( $name, '-' ) !== false ) {
                    list( $fieldName, $id ) = CRM_Utils_System::explode( '-', $name, 2 );
                    $detailName = str_replace( ' ', '_', $name );
                    if ( in_array( $fieldName, array( 'state_province', 'country', 'county' ) ) ) {
                        $values[$index] = $params[$detailName];
                        $idx = $detailName . '_id';
                        $values[$index] = $params[$idx];
                    } else if ( $fieldName == 'im' ) {
                        $providerName = null;
                        if ( $providerId = $detailName . '-provider_id' ) {
                            $providerName = CRM_Utils_Array::value( $params[$providerId], $imProviders );
                        }
                        if ( $providerName ) {
                            $values[$index] = $params[$detailName] . " (" . $providerName .")";
                        } else {
                            $values[$index] = $params[$detailName];
                        }
                    } else {
                        $values[$index] = $params[$detailName];
                    }
                } else {
                    if ( substr($name, 0, 7) === 'do_not_' or substr($name, 0, 3) === 'is_' ) {  
                        if ($params[$name] ) {
                            $values[$index] = '[ x ]';
                        }
                    } else {
                        require_once 'CRM/Core/BAO/CustomField.php';
                        if ( $cfID = CRM_Core_BAO_CustomField::getKeyID($name)) {
                            $query  = "
SELECT html_type, data_type
FROM   civicrm_custom_field
WHERE  id = $cfID
";
                            $dao = CRM_Core_DAO::executeQuery( $query,
                                                               CRM_Core_DAO::$_nullArray );
                            $dao->fetch( );
                            $htmlType  = $dao->html_type;
                            $dataType  = $dao->data_type;
                            
                            if ( $htmlType == 'File') {
                                //$fileURL = CRM_Core_BAO_CustomField::getFileURL( $contactID, $cfID );
                                //$params[$index] = $values[$index] = $fileURL['file_url'];
                                $values[$index] = $params[$index];
                            } else {
                                if ( $dao->data_type == 'Int' ||
                                     $dao->data_type == 'Boolean' ) {
                                    $customVal = (int ) ($params[$name]);
                                } else if ( $dao->data_type == 'Float' ) {
                                    $customVal = (float ) ($params[$name]);
                                } else if ( $dao->data_type == 'Date' ) {
                                    $date = CRM_Utils_Date::format( $params[$name], null, 'invalidDate' );
                                    if ( $date != 'invalidDate' ) {
                                        $customVal = $date;
                                    }
                                } else {
                                    $customVal = $params[$name];
                                }
                                //take the custom field options
                                $returnProperties = array( $name => 1 );
                                require_once 'CRM/Contact/BAO/Query.php';
                                $query   =& new CRM_Contact_BAO_Query( $params, $returnProperties, $fields );
                                $options =& $query->_options;
                                $displayValue = CRM_Core_BAO_CustomField::getDisplayValue( $customVal, $cfID, $options );
                                
                                //Hack since we dont have function to check empty.
                                //FIXME in 2.3 using crmIsEmptyArray()
                                $customValue = true;
                                if ( is_array($customVal) && is_array($displayValue) ) {
                                    $customValue = array_diff($customVal, $displayValue);
                                }
                                //use difference of arrays
                                if ( empty($customValue) || !$customValue ) {
                                    $values[$index] = ''; 
                                } else {
                                    $values[$index] = $displayValue;
                                }
                                
                                if ( CRM_Core_DAO::getFieldValue( 'CRM_Core_DAO_CustomField', 
                                                                  $cfID, 'is_search_range' ) ) {
                                    $customFieldName = "{$name}_from";
                                }
                            }
                        } else if ( $name == 'home_URL' &&
                                    ! empty( $params[$name] ) ) {
                            $url = CRM_Utils_System::fixURL( $params[$name] );
                            $values[$index] = "<a href=\"$url\">{$params[$name]}</a>";
                        } else if ( in_array( $name, array('birth_date', 'deceased_date','participant_register_date') ) ) {
                            require_once 'CRM/Utils/Date.php';
                            $values[$index] = CRM_Utils_Date::customFormat( CRM_Utils_Date::format( $params[$name] ) );
                        } else {
                            $values[$index] = $params[$name];
                        }
                    }
                }                   
            }
        }
    }
    
    /**  
     * Function to build the array for Additional participant's information  array of priamry and additional Ids 
     *  
     *@param int $participantId id of Primary participant
     *@param array $values key/value event info
     *@param int $contactId contact id of Primary participant 
     *@param boolean $isTest whether test or live transaction 
     *@param boolean $isIdsArray to return an array of Ids
     *
     *@return array $customProfile array of Additional participant's info OR array of Ids.   
     *@access public  
     */ 
    function buildCustomProfile( $participantId, $values, $contactId = null, $isTest = false, $isIdsArray = false ) 
    {
        $customProfile = $additionalIDs = array( );
        if ( !$participantId ) {
            CRM_Core_Error::fatal(ts('Cannot find participant ID'));
        }
                    
        //set Ids of Primary Participant also.
        if ( $isIdsArray && $contactId ) {
            $additionalIDs[$participantId] = $contactId; 
        }
        require_once 'CRM/Event/DAO/Participant.php';
        $participant   =  & new CRM_Event_DAO_Participant( );
        $participant->registered_by_id = $participantId;
        $participant->find();
        
        while ( $participant->fetch() ) {
            $additionalIDs[$participant->id] = $participant->contact_id;
        } 
        $participant->free( );
            
        //return if only array is required.
        if ( $isIdsArray && $contactId ) {
            return $additionalIDs;
        }
       
        //else build array of Additional participant's information. 
        if ( count($additionalIDs) ) { 
            if ( $values['custom_pre_id'] || $values['custom_post_id'] ) {
                $template =& CRM_Core_Smarty::singleton( );
                $isCustomProfile = true;
                $i = 1;
                foreach ( $additionalIDs as $pId => $cId ) {
                    $profilePre =  self::buildCustomDisplay( $values['custom_pre_id'], 'customPre',
                                                             $cId, $template, $pId, $isTest, $isCustomProfile );
                    if ( $profilePre ) {
                        $customProfile[$i]['customPre'] =  $profilePre;
                    }

                    $profilePost =  self::buildCustomDisplay( $values['custom_post_id'], 'customPost',
                                                              $cId, $template, $pId, $isTest, $isCustomProfile );
                    if ( $profilePost ) {
                        $customProfile[$i]['customPost'] =  $profilePost;
                    }
                    $i++;
                }
            }
        }

        return $customProfile;
    }
    
    /* Function to retrieve all events those having location block set.
     * 
     * @return array $events array of all events.
     */
    static function getLocationEvents( ) 
    {
        $events = array( );

        $query  = "
SELECT CONCAT_WS(' :: ' , ca.name, ca.street_address, ca.city, sp.name) title, ce.loc_block_id
FROM   civicrm_event ce
INNER JOIN civicrm_loc_block lb ON ce.loc_block_id = lb.id
INNER JOIN civicrm_address ca   ON lb.address_id = ca.id
LEFT  JOIN civicrm_state_province sp ON ca.state_province_id = sp.id
ORDER BY sp.name, ca.city, ca.street_address ASC
";
        
        $dao = CRM_Core_DAO::executeQuery( $query );
        while( $dao->fetch() ) {
            $events[$dao->loc_block_id] = $dao->title;
        }
        
        return $events;
    }

    static function countEventsUsingLocBlockId( $locBlockId )
    {
        if ( !$locBlockId ) {
            return 0;
        }

        $locBlockId = CRM_Utils_Type::escape( $locBlockId, 'Integer' );

        $query  = "
SELECT count(*) FROM civicrm_event ce
WHERE  ce.loc_block_id = $locBlockId";
        
        return CRM_Core_DAO::singleValueQuery( $query );
    }

    static function validRegistrationDate( &$values, $contactID ) {
        // make sure that we are between  registration start date and registration end date
        $startDate = CRM_Utils_Date::unixTime( CRM_Utils_Array::value( 'registration_start_date',
                                                                       $values['event'] ) );
        $endDate = CRM_Utils_Date::unixTime( CRM_Utils_Array::value( 'registration_end_date',
                                                                     $values['event'] ) );
        $now = time( );
        $validDate = true;
        if ( $startDate && $startDate >= $now ) {
            $validDate = false;
        }
        if ( $endDate && $endDate < $now ) {
            $validDate = false;
        }
        
        // also check that the user has permission to register for this event
        $hasPermission = CRM_Core_Permission::event( CRM_Core_Permission::EDIT,
                                                     $contactID );

        return $validDate && $hasPermission;
    }

}

