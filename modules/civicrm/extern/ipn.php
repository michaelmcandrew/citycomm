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

session_start( );

require_once '../civicrm.config.php';
require_once 'CRM/Core/Config.php';

/* Cache the real UF, override it with the SOAP environment */
$config =& CRM_Core_Config::singleton();

require_once 'CRM/Utils/Array.php';
$value = CRM_Utils_Array::value( 'module', $_GET );

require_once 'CRM/Core/Payment/PayPalIPN.php';

$paypalIPN = new CRM_Core_Payment_PayPalIPN( );

switch ( $value ) {
 case 'contribute':
     $paypalIPN->main( 'contribute' );
     break;
 case 'event':
     $paypalIPN->main( 'event' );
     break;
 default     :
     require_once 'CRM/Core/Error.php';
     CRM_Core_Error::debug_log_message( "Could not get module name from request url" );
     echo "Could not get module name from request url<p>";
     break;
 }


