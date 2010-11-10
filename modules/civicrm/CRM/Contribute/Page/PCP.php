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

require_once 'CRM/Core/Page/Basic.php';

/**
 * Page for displaying list of contribution types
 */
class CRM_Contribute_Page_PCP extends CRM_Core_Page_Basic 
{
    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_links = null;

    /**
     * Get BAO Name
     *
     * @return string Classname of BAO.
     */
    function getBAOName() 
    {
        return 'CRM_Contribute_BAO_PCP';
    }

    /**
     * Get action Links
     *
     * @return array (reference) of action links
     */
    function &links()
    {
        if (!(self::$_links)) {
            // helper variable for nicer formatting
            $deleteExtra = ts('Are you sure you want to delete this Campaign Page ?');

            self::$_links = array(
                                  CRM_Core_Action::ENABLE  => array(
                                                                    'name'  => ts('Approve'),
                                                                    'url'   => 'civicrm/admin/pcp',
                                                                    'qs'    => 'action=enable&id=%%id%%',
                                                                    'title' => ts('Approve Personal Campaign Page') 
                                                                    ),
                                  CRM_Core_Action::DISABLE  => array(
                                                                    'name'  => ts('Reject'),
                                                                    'url'   => 'civicrm/admin/pcp',
                                                                    'qs'    => 'action=disable&id=%%id%%',
                                                                    'title' => ts('Reject Personal Campaign Page') 
                                                                    ),
                                  CRM_Core_Action::DELETE  => array(
                                                                    'name'  => ts('Delete'),
                                                                    'url'   => 'civicrm/admin/pcp',
                                                                    'qs'    => 'action=delete&id=%%id%%',
                                                                    'extra' => 'onclick = "return confirm(\''. $deleteExtra . '\');"',
                                                                    'title' => ts('Delete Personal Campaign Page') 
                                                                    )
                                  );
        }
        return self::$_links;
    }


    /**
     * Browse all custom data groups.
     *  
     * 
     * @return void
     * @access public
     * @static
     */
    function browse( $action = null )
    {  
        require_once 'CRM/Contact/BAO/GroupNesting.php';
        $this->_sortByCharacter = CRM_Utils_Request::retrieve( 'sortByCharacter',
                                                               'String',
                                                               $this );        
        if ( $this->_sortByCharacter == 1 ||
             ! empty( $_POST ) ) {
            $this->_sortByCharacter = '';
        }

        require_once 'CRM/Contribute/PseudoConstant.php';
        $status            = CRM_Contribute_PseudoConstant::pcpstatus( );
        $contribution_page = CRM_Contribute_PseudoConstant::contributionPage( );
        $pcpSummary = $params = array();
        $whereClause = null;

        if ( ! empty ($_POST) ) {
            if ( $_POST['status_id'] != 0 ) {           
                $whereClause  = ' AND cp.status_id = %1';
                $params['1']  = array( $_POST['status_id'] , 'Integer' );
            }                

            if ( $_POST['contibution_page_id'] != 0 ){  
                $whereClause .=  ' AND cp.contribution_page_id = %2';
                $params['2']  = array( $_POST['contibution_page_id'] , 'Integer' );
            }
            
            if ( $_POST['status_id'] != 0 || $_POST['contibution_page_id'] != 0 ){
                $this->set( 'whereClause', $whereClause );
                $this->set( 'params', $params );
            } else {
                $this->set( 'whereClause', null );
                $this->set( 'params', null );
            }
        }
        $params = $this->get('params') ? $this->get('params') : array();
        $title       = ' AND cp.title LIKE %3';
        $params['3'] = array( $this->_sortByCharacter . '%', 'String' );        

        $query = "
        SELECT cp.id as id, contact_id , status_id, cp.title as title, contribution_page_id, start_date, end_date
        FROM civicrm_pcp cp, civicrm_contribution_page cpp
        WHERE cp.contribution_page_id = cpp.id $title". $this->get('whereClause') .
        " ORDER BY status_id";
        
        $dao = CRM_Core_DAO::executeQuery( $query, $params, true, 'CRM_Contribute_DAO_PCP' );

        while ( $dao->fetch( ) ) {
            
            $pcpSummary[$dao->id] = array();
            $action = array_sum(array_keys($this->links()));
            
            CRM_Core_DAO::storeValues( $dao, $pcpSummary[$dao->id] );
            
            require_once 'CRM/Contact/BAO/Contact.php';
            $contact = CRM_Contact_BAO_Contact::getDisplayAndImage( $dao->contact_id);
            
            switch ( $dao->status_id ) {
                
            case 2:                   
                $action -= CRM_Core_Action::ENABLE;
                break;
                
            case 3:                   
                $action -= CRM_Core_Action::DISABLE;
                break;
            }
            $pcpSummary[$dao->id]['id']                      = $dao->id;
            $pcpSummary[$dao->id]['start_date']              = $dao->start_date;
            $pcpSummary[$dao->id]['end_date']                = $dao->end_date;
            $pcpSummary[$dao->id]['supporter']               = $contact['0'];
            $pcpSummary[$dao->id]['supporter_id']            = $dao->contact_id;
            $pcpSummary[$dao->id]['status_id']               = $status[$dao->status_id];
            $pcpSummary[$dao->id]['contribution_page_id']    = $dao->contribution_page_id;
            $pcpSummary[$dao->id]['contribution_page_title'] = $contribution_page[$dao->contribution_page_id];
            $pcpSummary[$dao->id]['action']                  = CRM_Core_Action::formLink(self::links(), $action, 
                                                                                         array('id' => $dao->id));
        }

        $this->search( );   
        $this->pagerAToZ( $this->get('whereClause'), $params );
        if ( $pcpSummary ){ 
            $this->assign('rows', $pcpSummary);
        }
        // Let template know if user has run a search or not
        if ( $this->get('whereClause') ) {
            $this->assign('isSearch', 1);
        } else {
            $this->assign('isSearch', 0);
        }
    }

    function search( ) {
       
        if ( $this->_action & CRM_Core_Action::DELETE ) {
            return;
        }
        
        $form = new CRM_Core_Controller_Simple( 'CRM_Contribute_Form_PCP_PCP', ts( 'Search Campaign Pages' ), CRM_Core_Action::ADD );
        $form->setEmbedded( true );
        $form->setParent( $this );
        $form->process( );
        $form->run( );
    }
    
    /**
     * Get name of edit form
     *
     * @return string Classname of edit form.
     */
    function editForm( ) 
    { 
        return 'CRM_Contribute_Form_PCP_PCP';
    }
    
    /**
     * Get edit form name
     *
     * @return string name of this page.
     */
    function editName() 
    {
        return ts('Personal Campaign Page');
    }
    /**
     * return class name of delete form
     *
     * @return string
     * @access public
     */
    function deleteForm( ) 
    {
        return 'CRM_Contribute_Form_PCP_Delete';
    }
    
    /**
     * return name of delete form
     *
     * @return string
     * @access public
     */
    function deleteName( ) 
    {
        return ts('Delete Personal Campaign Page');
    }
    
    /**
     * Get user context.
     *
     * @return string user context.
     */
    function userContext($mode = null) 
    {
        return 'civicrm/admin/pcp';
    }

    function pagerAtoZ( $whereClause, $whereParams ) {
        require_once 'CRM/Utils/PagerAToZ.php';
        
        $query = "
 SELECT UPPER(LEFT(cp.title, 1)) as sort_name
     FROM civicrm_pcp cp, civicrm_contribution_page cpp
   WHERE cp.contribution_page_id = cpp.id $whereClause
 ORDER BY LEFT(cp.title, 1);
        ";
        $dao = CRM_Core_DAO::executeQuery( $query, $whereParams );
        
        $aToZBar = CRM_Utils_PagerAToZ::getAToZBar( $dao, $this->_sortByCharacter, true );
        $this->assign( 'aToZ', $aToZBar );
    }
}


