<?php

/*
 +--------------------------------------------------------------------+
 | CiviCRM version 3.2                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2010                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2010
 * $Id$
 *
 */

require_once 'CRM/Core/Page.php';

class CRM_Pledge_Page_Payment extends CRM_Core_Page
{
    /**
     * This function is the main function that is called when the page loads, it decides the which action has to be taken for the page.
     * 
     * return null
     * @access public
     */
    function run( ) 
    {
        $this->_action  = CRM_Utils_Request::retrieve('action', 'String', $this, false, 'browse');
        $this->_context = CRM_Utils_Request::retrieve('context', 'String', $this ) ;

        $this->assign( 'action', $this->_action );
        $this->assign( 'context', $this->_context );
        
        $this->_contactId = CRM_Utils_Request::retrieve( 'cid', 'Positive', $this );

        require_once 'CRM/Pledge/Page/Tab.php';
        CRM_Pledge_Page_Tab::setContext( );

        if ( $this->_action & CRM_Core_Action::UPDATE ) { 
            $this->edit( ); 
            // set page title
            require_once 'CRM/Contact/Page/View.php';
            CRM_Contact_Page_View::setTitle( $this->_contactId );
        } else {
            $pledgeId = CRM_Utils_Request::retrieve( 'pledgeId', 'Positive', $this );
            
            require_once 'CRM/Pledge/BAO/Payment.php';
            $paymentDetails = CRM_Pledge_BAO_Payment::getPledgePayments( $pledgeId );
            
            $this->assign( 'rows'     , $paymentDetails );
            $this->assign( 'pledgeId' , $pledgeId );
            $this->assign( 'contactId', $this->_contactId );
            
            // check if we can process credit card contribs
            $processors = CRM_Core_PseudoConstant::paymentProcessor( false, false,
                                                                     "billing_mode IN ( 1, 3 )" );
            if ( count( $processors ) > 0 ) {
                $this->assign( 'newCredit', true );
            } else {
                $this->assign( 'newCredit', false );
            }
            
            // check is the user has view/edit signer permission
            $permission = 'view';
            if ( CRM_Core_Permission::check( 'edit pledges' ) ) {
                $permission = 'edit';
            }
            $this->assign( 'permission', $permission );
        }

        return parent::run();
    }

    /** 
     * This function is called when action is update or new 
     *  
     * return null 
     * @access public 
     */ 
    function edit( ) 
    { 
        $controller = new CRM_Core_Controller_Simple( 'CRM_Pledge_Form_Payment', 
                                                       'Update Pledge Payment', 
                                                       $this->_action );

        $pledgePaymentId = CRM_Utils_Request::retrieve( 'ppId', 'Positive', $this );

        $controller->setEmbedded( true ); 
        $controller->set( 'id' , $pledgePaymentId ); 
        
        return $controller->run( );
    }


}


