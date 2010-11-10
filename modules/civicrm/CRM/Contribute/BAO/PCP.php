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

require_once 'CRM/Contribute/DAO/PCP.php';
require_once 'CRM/Contribute/DAO/PCPBlock.php';
require_once 'CRM/Contribute/DAO/Contribution.php';

class CRM_Contribute_BAO_PCP extends CRM_Contribute_DAO_PCP
{

    /**
     * The action links that we need to display for the browse screen
     *
     * @var array
     * @static
     */
    static $_pcpLinks = null;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * function to add the Personal Campaign Page Block
     *
     * @param array $params reference array contains the values submitted by the form
     * @param array $ids    reference array contains the id     * 
     * @access public
     * @static 
     * @return object
     */
    static function add(&$params) 
    {
        if (! isset($params['MAX_FILE_SIZE']) ) {
            // action is taken depending upon the mode
            require_once 'CRM/Contribute/DAO/PCPBlock.php';
            $dao =& new CRM_Contribute_DAO_PCPBlock( );
            $dao->copyValues( $params );
            $dao->save( );
            return $dao;
        } else {
            require_once 'CRM/Contribute/DAO/PCP.php';
            $dao              =& new CRM_Contribute_DAO_PCP( );
            $dao->copyValues( $params );

            // ensure we set status_id since it is a not null field
            // we should change the schema and allow this to be null
            if ( ! $dao->id &&
                 ! isset( $dao->status_id ) ) {
                $dao->status_id = 0;
            }

            $dao->save( );
            return $dao;
        }
    }
    
    /**
     * function to get the Display  name of a contact for a PCP
     *
     * @param  int    $id      id for the PCP
     *
     * @return null|string     Dispaly name of the contact if found
     * @static
     * @access public
     */
    static function displayName( $id ) 
    {
        $id = CRM_Utils_Type::escape( $id, 'Integer' );
        
        $query = "
SELECT civicrm_contact.display_name
FROM   civicrm_pcp, civicrm_contact
WHERE  civicrm_pcp.contact_id = civicrm_contact.id
  AND  civicrm_pcp.id = {$id}
";
        return CRM_Core_DAO::singleValueQuery( $query, CRM_Core_DAO::$_nullArray );
    }

    /**
     * Function to return PCP  Block info for dashboard
     * 
     * @return array     array of Pcp if found
     * @access public
     * @static
     */
    static function getPcpDashboardInfo( $contactId ) 
    {
        $links = self::pcpLinks();
        require_once 'CRM/Contribute/PseudoConstant.php';

        $query = "
        SELECT pg.start_date, pg.end_date, pg.title as pageTitle, pcp.id as pcpId, 
               pcp.title as pcpTitle, pcp.status_id as pcpStatusId, cov_status.label as pcpStatus,
               pcpblock.is_tellfriend_enabled as tellfriend, 
               pcpblock.id as blockId, pcp.is_active as pcpActive, pg.id as pageId
        FROM civicrm_contribution_page pg 
        LEFT JOIN civicrm_pcp pcp ON  (pg.id= pcp.contribution_page_id)
        LEFT JOIN civicrm_pcp_block as pcpblock ON ( pg.id = pcpblock.entity_id )
        
        LEFT JOIN civicrm_option_group cog_status ON cog_status.name = 'pcp_status'
        LEFT JOIN civicrm_option_value cov_status
               ON (pcp.status_id = cov_status.value
               AND cog_status.id = cov_status.option_group_id )
        
        INNER JOIN civicrm_contact as ct ON (ct.id = pcp.contact_id  AND pcp.contact_id = %1 )
        WHERE pcpblock.is_active = 1
        ORDER BY pcpStatus, pageTitle";

        $params = array( 1 => array( $contactId, 'Integer' ) );
        $pcpInfoDao = CRM_Core_DAO::executeQuery( $query, $params );
        $pcpInfo = array();
        $hide = $mask = array_sum( array_keys( $links['all'] ) );
        $contactPCPPages = array( );
        
        $approvedId = CRM_Core_OptionGroup::getValue( 'pcp_status', 'Approved', 'name' );
        while ( $pcpInfoDao->fetch( ) ) {
            $mask = $hide;
            if ( $links ) {
                $replace = array( 'pcpId'    => $pcpInfoDao->pcpId, 
                                  'pcpBlock'  => $pcpInfoDao->blockId);
            }
            $pcpLink = $links['all'];
            $class = '';

            if ( $pcpInfoDao->pcpStatusId != $approvedId || $pcpInfoDao->pcpActive != 1 ) {
                $class = "disabled";
            }
            if ( ! $pcpInfoDao->tellfriend || $pcpInfoDao->pcpStatusId != $approvedId ||  $pcpInfoDao->pcpActive != 1 ) {
                $mask -= CRM_Core_Action::DETACH;
            }
            if ( $pcpInfoDao->pcpActive == 1 ) {
                $mask -= CRM_Core_Action::ENABLE;
            } else {
                $mask -= CRM_Core_Action::DISABLE;
            }
            $action  = CRM_Core_Action::formLink( $pcpLink , $mask, $replace );
            $pcpInfo[] = array ( 
                                 'start_date'  => $pcpInfoDao->start_date,
                                 'end_date'    => $pcpInfoDao->end_date,
                                 'pageTitle'   => $pcpInfoDao->pageTitle,
                                 'pcpId'       => $pcpInfoDao->pcpId,
                                 'pcpTitle'    => $pcpInfoDao->pcpTitle,
                                 'pcpStatus'   => $pcpInfoDao->pcpStatus,
                                 'action'      => $action,
                                 'class'       => $class
                                  );
            $contactPCPPages[] = $pcpInfoDao->pageId;
        }

        $excludePageClause = null;
        if ( !empty( $contactPCPPages ) ) {
            $excludePageClause = " AND pg.id NOT IN ( " .implode( ',', $contactPCPPages ) . ") ";            
        }
        
        $query = "
        SELECT pg.id as pageId, pg.title as pageTitle, pg.start_date , 
                  pg.end_date 
        FROM civicrm_contribution_page pg 
        LEFT JOIN civicrm_pcp_block as pcpblock ON ( pg.id = pcpblock.entity_id )
        WHERE pcpblock.is_active = 1 {$excludePageClause}
        ORDER BY pageTitle ASC";

        $pcpBlockDao = CRM_Core_DAO::executeQuery( $query );
        $pcpBlock    = array();
        $mask  = 0;
        
        while ( $pcpBlockDao->fetch( ) ) {
            if ( $links ) {
                $replace = array( 'pageId' => $pcpBlockDao->pageId );
            }      
            $pcpLink = $links['add'];
            $action = CRM_Core_Action::formLink( $pcpLink , $mask, $replace );
            $pcpBlock[] = array ( 'pageId'     => $pcpBlockDao->pageId,
                                  'pageTitle'  => $pcpBlockDao->pageTitle,
                                  'start_date' => $pcpBlockDao->start_date,
                                  'end_date'   => $pcpBlockDao->end_date,
                                  'action'     => $action
                                  );
        }

        return  array( $pcpBlock, $pcpInfo );
    } 
    
    /**
     * function to show the total amount for Personal Campaign Page on thermometer
     *
     * @param array $pcpId  contains the pcp ID
     * 
     * @access public
     * @static 
     * @return total amount
     */
    static function thermoMeter( $pcpId ) 
    {
        $query = "
SELECT SUM(cc.total_amount) as total
FROM civicrm_pcp pcp 
LEFT JOIN civicrm_contribution_soft cs ON ( pcp.id = cs.pcp_id ) 
LEFT JOIN civicrm_contribution cc ON ( cs.contribution_id = cc.id)
WHERE pcp.id = %1 AND cc.contribution_status_id =1 AND cc.is_test = 0";
        
        $params = array( 1 => array( $pcpId, 'Integer' ) );
        return CRM_Core_DAO::singleValueQuery( $query, $params );
    }
    
    /**
     * Get action links
     *
     * @return array (reference) of action links
     * @static
     */
    static function &pcpLinks()
    {
        if (! ( self::$_pcpLinks ) ) {
            $deleteExtra = ts('Are you sure you want to delete this Personal Campaign Page ?\n This action can not be undone.');

            self::$_pcpLinks['add']  = array (
                                              CRM_Core_Action::ADD => array( 'name'  => ts('Create a Personal Campaign Page'),
                                                                             'url'   => 'civicrm/contribute/campaign',
                                                                             'qs'    => 'action=add&reset=1&pageId=%%pageId%%',
                                                                             'title' => ts('Configure')
                                                                             )
                                              );
            
            self::$_pcpLinks['all'] = array (
                                             CRM_Core_Action::UPDATE => array ( 'name'  => ts('Edit Your Page'),
                                                                                'url'   => 'civicrm/contribute/pcp/info',
                                                                                'qs'    => 'action=update&reset=1&id=%%pcpId%%',
                                                                                'title' => ts('Configure')
                                                                                ),
                                             CRM_Core_Action::DETACH => array ( 'name'  => ts('Tell Friends'),
                                                                               'url'   => 'civicrm/friend',
                                                                               'qs'    => 'eid=%%pcpId%%&blockId=%%pcpBlock%%&reset=1&page=pcp',
                                                                               'title' => ts('Tell Friends')
                                                                               ),
                                             CRM_Core_Action::BROWSE => array ( 'name'  => ts('Update Contact Information'),
                                                                                'url'   => 'civicrm/contribute/pcp/info',
                                                                                'qs'    => 'action=browse&reset=1&id=%%pcpId%%',
                                                                                'title' => ts('Update Contact Information')
                                                                                ),
                                             CRM_Core_Action::ENABLE => array ( 'name'  => ts('Enable'),
                                                                                'url'   => 'civicrm/contribute/pcp',
                                                                                'qs'    => 'action=enable&reset=1&id=%%pcpId%%',
                                                                                'title' => ts('Enable')
                                                                                ),
                                             CRM_Core_Action::DISABLE => array ( 'name'  => ts('Disable'),
                                                                                 'url'   => 'civicrm/contribute/pcp',
                                                                                 'qs'    => 'action=disable&reset=1&id=%%pcpId%%',
                                                                                 'title' => ts('Disable')
                                                                                 ),
                                             CRM_Core_Action::DELETE => array ( 'name'  => ts('Delete'),
                                                                                'url'   => 'civicrm/contribute/pcp',
                                                                                'qs'    => 'action=delete&reset=1&id=%%pcpId%%',
                                                                                'extra' => 'onclick = "return confirm(\''. $deleteExtra . '\');"',
                                                                                'title' => ts('Delete')
                                                                                )
                                             );
        }
        return self::$_pcpLinks;
    }

    /**
     * Function to Delete the campaign page
     * 
     * @param int $id campaign page id
     *
     * @return null
     * @access public
     * @static
     *
     */
    function delete ( $id ) 
    {
        require_once 'CRM/Utils/Hook.php';
        CRM_Utils_Hook::pre( 'delete', 'Campaign', $id, CRM_Core_DAO::$_nullArray );
        
        require_once 'CRM/Core/Transaction.php';
        $transaction = new CRM_Core_Transaction( );
        
        // delete from pcp table
        $pcp     =& new CRM_Contribute_DAO_PCP( );
        $pcp->id = $id;
        $pcp->delete( );
       
        $transaction->commit( );
        
        CRM_Utils_Hook::post( 'delete', 'Campaign', $id, $pcp );
    } 

    /**
     * Function to Approve / Reject the campaign page
     * 
     * @param int $id campaign page id
     *
     * @return null
     * @access public
     * @static
     *
     */
    static function setIsActive( $id, $is_active ) {
        switch ($is_active) 
        {
        case 0:
            $is_active = 3;
            break;
            
        case 1:
            $is_active = 2;
            break;
        }

        CRM_Core_DAO::setFieldValue( 'CRM_Contribute_DAO_PCP', $id, 'status_id', $is_active );

        require_once 'CRM/Contribute/PseudoConstant.php';
        $pcpTitle  = CRM_Core_DAO::getFieldValue( 'CRM_Contribute_DAO_PCP', $id, 'title' );
        $pcpStatus = CRM_Contribute_PseudoConstant::pcpStatus( );
        $pcpStatus = $pcpStatus[$is_active]; 

        CRM_Core_Session::setStatus("$pcpTitle status has been updated to $pcpStatus.");

        // send status change mail
        $result = self::sendStatusUpdate( $id, $is_active );

        if ( $result ) {
            CRM_Core_Session::setStatus("A notification email has been sent to the supporter.");
        }
    }

    /**
     * Function to send notfication email to supporter 
     * 1. when their PCP status is changed by site admin.
     * 2. when supporter initially creates a Personal Campaign Page ($isInitial set to true).
     * 
     * @param int $pcpId      campaign page id
     * @param int $newStatus  pcp status id
     * @param int $isInitial  is it the first time, campaign page has been created by the user
     *
     * @return null
     * @access public
     * @static
     *
     */
    static function sendStatusUpdate( $pcpId, $newStatus, $isInitial = false ) {
        require_once 'CRM/Contribute/PseudoConstant.php';
        $pcpStatus = CRM_Contribute_PseudoConstant::pcpStatus( );
        $config =& CRM_Core_Config::singleton( );

        if ( ! isset($pcpStatus[$newStatus]) ) {
            return false;
        }

        require_once 'CRM/Utils/Mail.php';
        require_once 'Mail/mime.php';
        require_once 'CRM/Contact/BAO/Contact/Location.php';        
        $template      =& CRM_Core_Smarty::singleton( );

        $emailTemplate = 'CRM/Contribute/Form/PCP/PCPStatusChange.tpl';
        if ( $isInitial ) {
            $emailTemplate = 'CRM/Contribute/Form/PCP/PCPSupporterNotify.tpl';
        }
        
        //set loginUrl
        $loginUrl = $config->userFrameworkBaseURL;
        switch ( ucfirst($config->userFramework) ) 
        {
        case 'Joomla' : 
            $loginUrl  = str_replace( 'administrator/', '', $loginUrl );
            $loginUrl .= 'index.php?option=com_user&view=login';
            break;
            
        case 'Drupal' :
            $loginUrl .= 'user';
            break;
        }
        
        $template->assign( 'loginUrl', $loginUrl );
        
        // set appropriate subject
        $contribPageTitle = self::getPcpContributionPageTitle( $pcpId );
        $subject  = "Your Personal Campaign Page for $contribPageTitle";

        //get the default domain email address.
        require_once 'CRM/Core/BAO/Domain.php';
        list( $domainEmailName, $domainEmailAddress ) = CRM_Core_BAO_Domain::getNameAndEmail( );
        
        if ( !$domainEmailAddress || $domainEmailAddress == 'info@FIXME.ORG') {
            CRM_Core_Error::fatal( ts( 'The site administrator needs to enter a valid \'FROM Email Address\' in Administer CiviCRM &raquo; Configure &raquo; Domain Information. The email address used may need to be a valid mail account with your email service provider.' ) );
        }
            
        $receiptFrom = '"' . $domainEmailName . '" <' . $domainEmailAddress . '>';

        // get recipient (supporter) name and email
        $params = array( 'id' => $pcpId );
        CRM_Core_DAO::commonRetrieve('CRM_Contribute_DAO_PCP', $params, $pcpInfo);
        list ($name, $address) = 
            CRM_Contact_BAO_Contact_Location::getEmailDetails( $pcpInfo['contact_id'] );

        // get pcp block info
        list($blockId, $eid) = self::getPcpBlockEntityId( $pcpId );
        $params = array( 'id' => $blockId );
        CRM_Core_DAO::commonRetrieve('CRM_Contribute_DAO_PCPBlock', $params, $pcpBlockInfo);

        // assign urls required in email template
        if ( $pcpStatus[$newStatus] == 'Approved' ) {
            $template->assign( 'isTellFriendEnabled', $pcpBlockInfo['is_tellfriend_enabled'] );
            if ( $pcpBlockInfo['is_tellfriend_enabled'] ) {
                $pcpTellFriendURL = 
                    CRM_Utils_System::url('civicrm/friend', 
                                          "reset=1&eid=$eid&blockId=$blockId&page=pcp", 
                                          true, null, false);
                $template->assign( 'pcpTellFriendURL', $pcpTellFriendURL );
            }
        }
        $pcpInfoURL = CRM_Utils_System::url('civicrm/contribute/pcp/info', 
                                            "reset=1&id=$pcpId", 
                                            true, null, false);
        $template->assign( 'pcpInfoURL', $pcpInfoURL );
        $template->assign( 'contribPageTitle', $contribPageTitle );
        if ( $emails = CRM_Utils_Array::value( 'notify_email', $pcpBlockInfo ) ) {
            $emailArray = explode(',', $emails );
            $template->assign( 'pcpNotifyEmailAddress', $emailArray[0] );
        }
        // get appropriate message based on status
        $template->assign( 'pcpStatus', $pcpStatus[$newStatus] );
        $message       = $template->fetch( $emailTemplate );
        
        return CRM_Utils_Mail::send( $receiptFrom,
                                     $name,
                                     $address,
                                     $subject,
                                     $message
                                     );
    }

    /**
     * Function to Enable / Disable the campaign page
     * 
     * @param int $id campaign page id
     *
     * @return null
     * @access public
     * @static
     *
     */
    static function setDisable( $id, $is_active ) {
        return CRM_Core_DAO::setFieldValue( 'CRM_Contribute_DAO_PCP', $id, 'is_active', $is_active );
    }

    /**
     * Function to get pcp block is active 
     * 
     * @param int $id campaign page id
     *
     * @return int
     * @access public
     * @static
     *
     */
    static function getStatus( $pcpId ) 
    {
        $query = "
     SELECT pb.is_active 
     FROM civicrm_pcp pcp 
          LEFT JOIN civicrm_pcp_block pb ON ( pcp.contribution_page_id = pb.entity_id )
          LEFT JOIN civicrm_contribution_page as cp ON ( cp.id =  pcp.contribution_page_id )
     WHERE pcp.id = %1";
        
        $params = array( 1 => array( $pcpId, 'Integer' ) );
        return CRM_Core_DAO::singleValueQuery( $query, $params );
    }

    /**
     * Function to get pcp block is enabled for contribution page 
     * 
     * @param int $id contribution page id
     *
     * @return String
     * @access public
     * @static
     *
     */
    static function getPcpBlockStatus( $pageId ) 
    {
        $query = "
     SELECT pb.link_text as linkText
     FROM civicrm_contribution_page cp 
          LEFT JOIN civicrm_pcp_block pb ON ( cp.id = pb.entity_id AND pb.entity_table = 'civicrm_contribution_page' )
     WHERE pb.is_active = 1 AND cp.id = %1";
        
        $params = array( 1 => array( $pageId, 'Integer' ) );
        return CRM_Core_DAO::singleValueQuery( $query, $params );
    }

    /**
     * Function to get email is enabled for supporter's profile 
     * 
     * @param int $id supporter's profile id
     *
     * @return boolean
     * @access public
     * @static
     *
     */
    static function checkEmailProfile( $profileId ) 
    {
        $query="
SELECT field_name
FROM civicrm_uf_field
WHERE field_name like 'email%' And is_active = 1 And uf_group_id = %1";

        $params = array( 1 => array( $profileId, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        if ( ! $dao->fetch() ){
            return true;
        }
        return false;
    }

    /**
     * Function to obtain the title of contribution page associated with a pcp
     * 
     * @param int $id campaign page id
     *
     * @return int
     * @access public
     * @static
     *
     */
    static function getPcpContributionPageTitle( $pcpId ) 
    {
        $query = "
SELECT cp.title 
FROM civicrm_pcp pcp 
LEFT JOIN civicrm_contribution_page as cp ON ( cp.id =  pcp.contribution_page_id )
WHERE pcp.id = %1";
        
        $params = array( 1 => array( $pcpId, 'Integer' ) );
        return CRM_Core_DAO::singleValueQuery( $query, $params );
    }

    /**
     * Function to get pcp block & entity id given pcp id
     * 
     * @param int $id campaign page id
     *
     * @return String
     * @access public
     * @static
     *
     */
    static function getPcpBlockEntityId( $pcpId ) 
    {
        $query = "
SELECT pb.id as pcpBlockId, pb.entity_id
FROM civicrm_pcp pcp 
LEFT JOIN civicrm_pcp_block pb ON ( pb.entity_id = pcp.contribution_page_id AND pb.entity_table = 'civicrm_contribution_page' )
WHERE pcp.id = %1";

        $params = array( 1 => array( $pcpId, 'Integer' ) );
        $dao = CRM_Core_DAO::executeQuery( $query, $params );
        if ( $dao->fetch() ){
            return array($dao->pcpBlockId, $dao->entity_id);
        }

        return array( );
    }
}
