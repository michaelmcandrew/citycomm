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

require_once 'CRM/Admin/Form/Setting.php';

/**
 * This class generates form components for Miscellaneous
 * 
 */
class CRM_Admin_Form_Setting_Miscellaneous extends  CRM_Admin_Form_Setting
{
    /**
     * Function to build the form
     *
     * @return None
     * @access public
     */
    public function buildQuickForm( ) {
        CRM_Utils_System::setTitle(ts('Settings - Miscellaneous'));

        $this->addYesNo( 'versionCheck'           , ts( 'Version Check & Statistics Reporting' ));
        $this->addYesNo( 'includeWildCardInName'  , ts( 'Automatic Wildcard' ));
        $this->addYesNo( 'includeEmailInName'     , ts( 'Include Email' ));
        $this->addYesNo( 'includeNickNameInName'  , ts( 'Include Nickname' ));
        $this->addElement('text', 'smartGroupCacheTimeout', ts('Smart group cache timeout'),
                          array( 'size' => 3, 'maxlength' => 5 ) );
        $this->addElement('text', 'maxAttachments' , ts('Maximum Attachments'),
                          array( 'size' => 2, 'maxlength' => 8 ) );
        $this->addElement('text','recaptchaPublicKey' , ts('Public Key'),
                          array( 'size' => 64, 'maxlength' => 64 ) );
        $this->addElement('text','recaptchaPrivateKey', ts('Private Key'),
                          array( 'size' => 64, 'maxlength' => 64 ) );
       
        parent::buildQuickForm();    
    }
}


