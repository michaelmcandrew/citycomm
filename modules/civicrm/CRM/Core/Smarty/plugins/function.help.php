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
 * Replace the value of an attribute in the input string. Assume
 * the the attribute is well formed, of the type name="value". If
 * no replacement is mentioned the value is inserted at the end of
 * the form element
 *
 * @param array  $params the function params
 * @param object $smarty reference to the smarty object 
 *
 * @return string the help html to be inserted
 * @access public
 */
function smarty_function_help( $params, &$smarty ) {
    if ( ! isset( $params['id'] ) || ! isset( $smarty->_tpl_vars[ 'config'] ) ) {
        return;
    }

    if ( isset( $params['file'] ) ) {
        $file = $params['file'];
    } else if ( isset( $smarty->_tpl_vars[ 'tplFile' ] ) ) {
        $file = $smarty->_tpl_vars[ 'tplFile' ];
    } else {
        return;
    }
    
    $file = str_replace( '.tpl', '.hlp', $file );
    $id   = urlencode( $params['id'] );
    if ( $id =='accesskeys') {
        $file ='CRM/common/accesskeys.hlp';
    }
        
    $smarty->assign( 'id', $params['id'] );
    $help = $smarty->fetch( $file );
    return <<< EOT

<div class="helpicon" style="display: inline;"><span dojoType="dijit.form.DropDownButton" class="tundra">
    <div><img src="{$smarty->_tpl_vars[ 'config']->resourceBase}i/quiz.png" /></div>
    <div dojoType="dijit.TooltipDialog" id="{$id}_help" class="tundra" >$help</div>
</span></div>
EOT;

}


