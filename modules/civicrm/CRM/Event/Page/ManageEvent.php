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

require_once 'CRM/Core/Page.php';

/**
 * Page for displaying list of events
 */
class CRM_Event_Page_ManageEvent extends CRM_Core_Page
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_actionLinks = null;

    static $_links = null;

    protected $_pager = null;

    protected $_sortByCharacter;

    /**
     * Get action Links
     *
     * @return array (reference) of action links
     */
    function &links()
    {
        if (!(self::$_actionLinks)) {
            // helper variable for nicer formatting
            $disableExtra = ts('Are you sure you want to disable this Event?');
            $deleteExtra = ts('Are you sure you want to delete this Event?');
            $copyExtra = ts('Are you sure you want to make a copy of this Event?');

            self::$_actionLinks = array(
                                        CRM_Core_Action::UPDATE  => array(
                                                                          'name'  => ts('Configure'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'action=update&id=%%id%%&reset=1',
                                                                          'title' => ts('Configure Event') 
                                                                          ),
                                        CRM_Core_Action::PREVIEW => array(
                                                                          'name'  => ts('Test-drive'),
                                                                          'url'   => 'civicrm/event/info',
                                                                          'qs'    => 'reset=1&action=preview&id=%%id%%',
                                                                          'title' => ts('Preview') 
                                                                          ),
                                        CRM_Core_Action::FOLLOWUP    => array(
                                                                          'name'  => ts('Live Page'),
                                                                          'url'   => 'civicrm/event/info',
                                                                          'qs'    => 'reset=1&id=%%id%%',
                                                                          'title' => ts('FollowUp'),
                                                                          ),
                                        CRM_Core_Action::DISABLE => array(
                                                                          'name'  => ts('Disable'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'action=disable&id=%%id%%',
                                                                          'extra' => 'onclick = "return confirm(\'' . $disableExtra . '\');"',
                                                                          'title' => ts('Disable Event') 
                                                                          ),
                                        CRM_Core_Action::ENABLE  => array(
                                                                          'name'  => ts('Enable'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'action=enable&id=%%id%%',
                                                                          'title' => ts('Enable Event') 
                                                                          ),
                                        CRM_Core_Action::DELETE  => array(
                                                                          'name'  => ts('Delete'),
                                                                          'url'   => CRM_Utils_System::currentPath( ),
                                                                          'qs'    => 'action=delete&id=%%id%%',
                                                                          'extra' => 'onclick = "return confirm(\'' . $deleteExtra . '\');"',
                                                                          'title' => ts('Delete Event') 
                                                                          ),
                                        CRM_Core_Action::COPY     => array(
                                                                           'name'  => ts('Copy Event'),
                                                                           'url'   => CRM_Utils_System::currentPath( ),                                                                                                'qs'    => 'reset=1&action=copy&id=%%id%%',
                                                                           'extra' => 'onclick = "return confirm(\'' . $copyExtra . '\');"',
                                                                           'title' => ts('Copy Event') 
                                                                          )
                                        );
        }
        return self::$_actionLinks;
    }

    /**
     * Run the page.
     *
     * This method is called after the page is created. It checks for the  
     * type of action and executes that action.
     * Finally it calls the parent's run method.
     *
     * @return void
     * @access public
     *
     */
    function run()
    {
        // get the requested action
        $action = CRM_Utils_Request::retrieve('action', 'String',
                                              $this, false, 'browse'); // default to 'browse'
        
        // assign vars to templates
        $this->assign('action', $action);
        $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                          $this, false, 0);
        
        // set breadcrumb to append to 2nd layer pages
        $breadCrumb = array ( array('title' => ts('Manage Events'),
                                    'url'   => CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 
                                                                      'reset=1' )) );

        // what action to take ?
        if ( $action & CRM_Core_Action::ADD ) {
            $session =& CRM_Core_Session::singleton( ); 
            
            $title = "New Event Wizard";
            $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'reset=1' ) );
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );
            CRM_Utils_System::setTitle( $title );
            
            require_once 'CRM/Event/Controller/ManageEvent.php';
            $controller =& new CRM_Event_Controller_ManageEvent( );
            return $controller->run( );
        } else if ($action & CRM_Core_Action::UPDATE ) {
            CRM_Utils_System::appendBreadCrumb( $breadCrumb );

            require_once 'CRM/Event/Page/ManageEventEdit.php';
            $page =& new CRM_Event_Page_ManageEventEdit( );
            return $page->run( );
        } else if ($action & CRM_Core_Action::DISABLE ) {
            require_once 'CRM/Event/BAO/Event.php';
            CRM_Event_BAO_Event::setIsActive($id ,0);
        } else if ($action & CRM_Core_Action::ENABLE ) {
            require_once 'CRM/Event/BAO/Event.php';
            CRM_Event_BAO_Event::setIsActive($id ,1); 
        } else if ($action & CRM_Core_Action::DELETE ) {
            $session =& CRM_Core_Session::singleton();
            $session->pushUserContext( CRM_Utils_System::url( CRM_Utils_System::currentPath( ), 'reset=1&action=browse' ) );
            $controller =& new CRM_Core_Controller_Simple( 'CRM_Event_Form_ManageEvent_Delete',
                                                           'Delete Event',
                                                           $action );
            $id = CRM_Utils_Request::retrieve('id', 'Positive',
                                              $this, false, 0);
            $controller->set( 'id', $id );
            $controller->process( );
            return $controller->run( );
        } else if ($action & CRM_Core_Action::COPY ) {
            $this->copy( );
        }

        // finally browse the custom groups
        $this->browse();
        
        // parent run 
        parent::run();
    }

    /**
     * Browse all custom data groups.
     *  
     * 
     * @return void
     * @access public
     * @static
     */
    function browse()
    {

        $this->_sortByCharacter = CRM_Utils_Request::retrieve( 'sortByCharacter',
                                                               'String',
                                                               $this );
        if ( $this->_sortByCharacter == 1 ||
             ! empty( $_POST ) ) {
            $this->_sortByCharacter = '';
            $this->set( 'sortByCharacter', '' );
        }

        $this->_force = null;
        $this->_searchResult = null;
      
        $this->search( );

        $config =& CRM_Core_Config::singleton( );
        
        $params = array( );
        $this->_force = CRM_Utils_Request::retrieve( 'force', 'Boolean',
                                                       $this, false ); 
        $this->_searchResult = CRM_Utils_Request::retrieve( 'searchResult', 'Boolean', $this );
      
        $whereClause = $this->whereClause( $params, false, $this->_force );
        $this->pagerAToZ( $whereClause, $params );

        $params      = array( );
        $whereClause = $this->whereClause( $params, true, $this->_force );

        $this->pager( $whereClause, $params );
        list( $offset, $rowCount ) = $this->_pager->getOffsetAndRowCount( );

        // get all custom groups sorted by weight
        $manageEvent = array();
             
        $query = "
  SELECT *
    FROM civicrm_event
   WHERE $whereClause
ORDER BY start_date desc
   LIMIT $offset, $rowCount";
        
        $dao = CRM_Core_DAO::executeQuery( $query, $params, true, 'CRM_Event_DAO_Event' );
     
        while ($dao->fetch()) {
            $manageEvent[$dao->id] = array();
            CRM_Core_DAO::storeValues( $dao, $manageEvent[$dao->id]);
            
            // form all action links
            $action = array_sum(array_keys($this->links()));
            
            if ($dao->is_active) {
                $action -= CRM_Core_Action::ENABLE;
            } else {
                $action -= CRM_Core_Action::DISABLE;
            }
            
            $manageEvent[$dao->id]['action'] = CRM_Core_Action::formLink(self::links(), $action, 
                                                                         array('id' => $dao->id));

            $params = array( 'entity_id' => $dao->id, 'entity_table' => 'civicrm_event');
            require_once 'CRM/Core/BAO/Location.php';
            $location = CRM_Core_BAO_Location::getValues($params, $defaults );
            if ( isset ( $defaults['location'][1]['address']['city'] ) ) {
                $manageEvent[$dao->id]['city'] = $defaults['location'][1]['address']['city'];
            }
            if ( isset( $defaults['location'][1]['address']['state_province_id'] )) {
                $manageEvent[$dao->id]['state_province'] = CRM_Core_PseudoConstant::stateProvince($defaults['location'][1]['address']['state_province_id']);
            }
        }
        $this->assign('rows', $manageEvent);
    }
    
    /**
     * This function is to make a copy of a Event, including
     * all the fields in the event wizard
     *
     * @return void
     * @access public
     */
    function copy( )
    {
        $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, true, 0, 'GET');
        
        require_once 'CRM/Event/BAO/Event.php';
        CRM_Event_BAO_Event::copy( $id );

        return CRM_Utils_System::redirect( CRM_Utils_System::url( 'civicrm/event/manage', 'reset=1' ) );
    }


    function search( ) {
        if ( isset($this->_action) &
             ( CRM_Core_Action::ADD    |
               CRM_Core_Action::UPDATE |
               CRM_Core_Action::DELETE ) ) {
            return;
        }
       
        $form = new CRM_Core_Controller_Simple( 'CRM_Event_Form_SearchEvent', ts( 'Search Events' ), CRM_Core_Action::ADD );
        $form->setEmbedded( true );
        $form->setParent( $this );
        $form->process( );
        $form->run( );
    }
    
    function whereClause( &$params, $sortBy = true, $force ) {
        $values  =  array( );
        $clauses = array( );
        $title   = $this->get( 'title' );
        if ( $title ) {
            $clauses[] = "title LIKE %1";
            if ( strpos( $title, '%' ) !== false ) {
                $params[1] = array( trim($title), 'String', false );
            } else {
                $params[1] = array( trim($title), 'String', true );
            }
        }

        $value = $this->get( 'event_type_id' );
        $val = array( );
        if( $value) {
            if ( is_array( $value ) ) {
                foreach ($value as $k => $v) {
                    if ($v) {
                        $val[$k] = $k;
                    }
                } 
                $type = implode (',' ,$val);
            }






            
            $clauses[] = "event_type_id IN ({$type})";
        }
        
        $eventsByDates = $this->get( 'eventsByDates' );
        if ($this->_searchResult) {
            if ( $eventsByDates) {
                require_once 'CRM/Utils/Date.php';
                
                $from = $this->get( 'start_date' );
                if ( ! CRM_Utils_System::isNull( $from ) ) {
                    $from = CRM_Utils_date::format( $from );
                    $from .= '000000';
                    $clauses[] = '( start_date >= %3 OR start_date IS NULL )';
                    $params[3] = array( $from, 'String' );
                }
                
                $to = $this->get( 'end_date' );
                if ( ! CRM_Utils_System::isNull( $to ) ) {
                    $to = CRM_Utils_date::format( $to );
                    $to .= '235959';
                    $clauses[] = '( end_date <= %4 OR end_date IS NULL )';
                    $params[4] = array( $to, 'String' );
                }
                
            } else {
                $curDate = date( 'YmdHis' );
                $clauses[5] =  "(end_date >= {$curDate} OR end_date IS NULL)";
            }
        
        } else {
            $curDate = date( 'YmdHis' );
            $clauses[] =  "(end_date >= {$curDate} OR end_date IS NULL)";
        }

        if ( $sortBy &&
             $this->_sortByCharacter ) {
            $clauses[] = 'title LIKE %6';
            $params[6] = array( $this->_sortByCharacter . '%', 'String' );
        }

        // dont do a the below assignment when doing a 
        // AtoZ pager clause
        if ( $sortBy ) {
            if ( count( $clauses ) > 1 || $eventsByDates  ) {
                $this->assign( 'isSearch', 1 );
            } else {
                $this->assign( 'isSearch', 0 );
            }
        }

        require_once 'CRM/Core/Permission.php';
        $clauses[] = CRM_Core_Permission::eventClause( );

        return implode( ' AND ', $clauses );
    }


     function pager( $whereClause, $whereParams ) {
        require_once 'CRM/Utils/Pager.php';

        $params['status']       = ts('Event %%StatusMessage%%');
        $params['csvString']    = null;
        $params['buttonTop']    = 'PagerTopButton';
        $params['buttonBottom'] = 'PagerBottomButton';
        $params['rowCount']     = $this->get( CRM_Utils_Pager::PAGE_ROWCOUNT );
        if ( ! $params['rowCount'] ) {
            $params['rowCount'] = CRM_Utils_Pager::ROWCOUNT;
        }

        $query = "
SELECT count(id)
  FROM civicrm_event
 WHERE $whereClause";

        $params['total'] = CRM_Core_DAO::singleValueQuery( $query, $whereParams );
            
        $this->_pager = new CRM_Utils_Pager( $params );
        $this->assign_by_ref( 'pager', $this->_pager );
    }

    function pagerAtoZ( $whereClause, $whereParams ) {
        require_once 'CRM/Utils/PagerAToZ.php';
        
        $query = "
   SELECT DISTINCT UPPER(LEFT(title, 1)) as sort_name
     FROM civicrm_event
    WHERE $whereClause
 ORDER BY LEFT(title, 1)
";
        $dao = CRM_Core_DAO::executeQuery( $query, $whereParams );

        $aToZBar = CRM_Utils_PagerAToZ::getAToZBar( $dao, $this->_sortByCharacter, true );
        $this->assign( 'aToZ', $aToZBar );
    }
    
}

