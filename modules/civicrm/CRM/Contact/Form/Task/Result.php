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

require_once 'CRM/Contact/Form/Task.php';

/**
 * Used for displaying results
 *
 *
 */
class CRM_Contact_Form_Task_Result extends CRM_Contact_Form_Task {

    /**
     * build all the data structures needed to build the form
     *
     * @return void
     * @access public
     */
    function preProcess( ) {
        $session =& CRM_Core_Session::singleton( );
        
        //this is done to unset searchRows variable assign during AddToHousehold and AddToOrganization
        $this->set( 'searchRows', '');

        $context = $this->get( 'context' );
        if ( $context == 'smog' || $context == 'amtg' ) {
            $url = CRM_Utils_System::url( 'civicrm/group/search', 'reset=1&force=1&context=smog&gid=' );
            if ( $this->get( 'context' ) == 'smog' ) {
                $session->replaceUserContext( $url . $this->get( 'gid'    ) );
            } else {
                $session->replaceUserContext( $url . $this->get( 'amtgID' ) );
            }
            return;
        }

        $ssID = $this->get( 'ssID' );
        
        if ( $this->_action == CRM_Core_Action::BASIC ) {
            $fragment = 'search';
        } else if ( $this->_action == CRM_Core_Action::PROFILE ) {
            $fragment = 'search/builder';
        } else if ( $this->_action == CRM_Core_Action::ADVANCED ) {
            $fragment = 'search/advanced';
        } else {
            $fragment = 'search/custom';
        }
        
        $path = 'force=1';
        if ( isset( $ssID ) ) {
            $path .= "&reset=1&ssID={$ssID}";
        }
            
        $url = CRM_Utils_System::url( 'civicrm/contact/' . $fragment, $path );
        $session->replaceUserContext( $url );
        return;

    }

    /**
     * Function to actually build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        $this->addButtons( array(
                                 array ( 'type'      => 'done',
                                         'name'      => ts('Done'),
                                         'isDefault' => true   ),
                                 )
                           );
    }

}

