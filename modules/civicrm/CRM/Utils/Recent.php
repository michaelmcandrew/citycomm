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
 *
 */
class CRM_Utils_Recent {
    
    /**
     * max number of items in queue
     *
     * @int
     */
    const
        MAX_ITEMS  = 5,
        STORE_NAME = 'CRM_Utils_Recent';

    /**
     * The list of recently viewed items
     *
     * @var array
     * @static
     */
    static private $_recent = null;

    /**
     * initialize this class and set the static variables
     *
     * @return void
     * @access public
     * @static
     */
    static function initialize( ) {
        if ( ! self::$_recent ) {
            $session =& CRM_Core_Session::singleton( );
            self::$_recent = $session->get( self::STORE_NAME );
            if ( ! self::$_recent ) {
                self::$_recent = array( );
            }
        }
    }

    /**
     * return the recently viewed array
     *
     * @return array the recently viewed array
     * @access public
     * @static
     */
    static function &get( ) {
        self::initialize( );
        return self::$_recent;
    }

    /**
     * add an item to the recent stack
     *
     * @param string $title  the title to display
     * @param string $url    the link for the above title
     * @param string $icon   a link to a graphical image
     * @param string $id     contact id
     *
     * @return void
     * @access public
     * @static
     */
    static function add( $title, $url, $icon, $id ) {
        self::initialize( );

        $session =& CRM_Core_Session::singleton( );

        // make sure item is not already present in list
        for ( $i = 0; $i < count( self::$_recent ); $i++ ) {
            if ( self::$_recent[$i]['url' ] == $url ) {
                // delete item from array
                array_splice( self::$_recent, $i, 1 );
                break;
            }
        }
        
        array_unshift( self::$_recent,
                       array( 'title' => $title, 
                              'url'   => $url,
                              'icon'  => $icon,
                              'id'  => $id ) );
        if ( count( self::$_recent ) > self::MAX_ITEMS ) {
            array_pop( self::$_recent );
        }

        $session->set( self::STORE_NAME, self::$_recent );
    }

    /**
     * delete an item from the recent stack
     *
     * @param string $id  contact id that had to be removed
     *
     * @return void
     * @access public
     * @static
     */
    static function del( $id ) {
        self::initialize( );

        $tempRecent = self::$_recent;
        
        self::$_recent = '';
        
        // make sure item is not already present in list
        for ( $i = 0; $i < count( $tempRecent ); $i++ ) {
            if ( $tempRecent[$i]['id' ] != $id ) {
                self::$_recent[] = $tempRecent[$i];
            }
        }
        
        $session =& CRM_Core_Session::singleton( );
        $session->set( self::STORE_NAME, self::$_recent );
    }

}


