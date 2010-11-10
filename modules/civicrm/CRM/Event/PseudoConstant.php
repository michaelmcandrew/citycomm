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

/**
 * This class holds all the Pseudo constants that are specific to Event. This avoids
 * polluting the core class and isolates the Event
 */
class CRM_Event_PseudoConstant extends CRM_Core_PseudoConstant 
{
    /**
     * Event
     *
     * @var array
     * @static
     */
    private static $event; 
    
    /**
     * Participant Status 
     *
     * @var array
     * @static
     */
    private static $participantStatus; 
    
    /**
     * Participant Role
     *
     * @var array
     * @static
     */
    private static $participantRole; 
    
    /**
     * Get all the n events
     *
     * @access public
     * @return array - array reference of all events if any
     * @static
     */
    public static function &event( $id = null, $all = false )
    {
        if ( !isset( self::$event[$all] ) ) {
            self::$event[$all] = array( );
        }

        if ( ! self::$event[$all] ) {
            CRM_Core_PseudoConstant::populate( self::$event[$all],
                                               'CRM_Event_DAO_Event',
                                               $all, 'title', 'is_active', null, null);
        }
                        
        if ($id) {
            if (array_key_exists($id, self::$event[$all])) {
                return self::$event[$all][$id];
            } else {
                return null;
            }
        }
        return self::$event[$all];
    }
    
    /**
     * Get all the n participant statuses
     *
     * @access public
     * @return array - array reference of all participant statuses if any
     * @static
     */
    public static function &participantStatus( $id = null, $cond = null ) 
    {
        if ( self::$participantStatus === null ) {
            self::$participantStatus = array( );
        }

        $index = $cond ? $cond : 'No Condition';
        if ( ! CRM_Utils_Array::value( $index, self::$participantStatus ) ) {
            self::$participantStatus[$index] = array( );
            require_once "CRM/Core/OptionGroup.php";
            $condition = null;

            if ( $cond ) {
                $condition = "AND $cond";
            }
            self::$participantStatus[$index] = CRM_Core_OptionGroup::values("participant_status", false, false, false, $condition);
        }
        
        if ( $id ) {
            return self::$participantStatus[$index][$id];
        }
        
        return self::$participantStatus[$index];
    }
    
    /**
     * Get all the n participant roles
     *
     * @access public
     * @return array - array reference of all participant roles if any
     * @static
     */
    public static function &participantRole( $id = null )
    {
        if ( ! self::$participantRole ) {
            self::$participantRole = array( );
            require_once "CRM/Core/OptionGroup.php";
            self::$participantRole = CRM_Core_OptionGroup::values("participant_role");
        }
        
        If( $id ) {
            return self::$participantRole[$id];
        }
        
        return self::$participantRole;
    }
}

