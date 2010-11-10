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

require_once 'CRM/Report/Form.php';

class CRM_Report_Form_Activity extends CRM_Report_Form {

    protected $_addressField  = false;

    protected $_emailField    = false;

    protected $_assigneeField = false;

    protected $_summary       = null;

    function __construct( ) {
        $this->_columns = array( 'civicrm_contact'      =>
                                 array( 'dao'     => 'CRM_Contact_DAO_Contact',
                                        'fields'  =>
                                        array( 'display_name' => array( 'title' => ts( 'Source Contact Name' ),
                                                                        'required'  => true,
                                                                        'no_repeat' => true ),
                                               ),
                                        
                                        'filters' =>             
                                        array('sort_name'    => 
                                              array( 'title'      => ts( 'Contact Name' ),
                                                     'operator'   => 'like' ) ),
                                        'grouping'=> 'contact-fields',
                                        'order_bys'=>             
                                        array( 'display_name' => array( 'title' => ts( 'Contact Name' ),
                                                                        'required'  => true ) ),

                                        ),
                                 
                                 'civicrm_activity'      =>
                                 array( 'dao'     => 'CRM_Activity_DAO_Activity',
                                        'fields'  =>
                                        array(
                                              'activity_type_id' => array( 'title'   => ts( 'Activity Type'),
                                                                           'default' => true ),
                                              'subject' => array( 'default' => true ),
                                              'activity_date_time' => array( 'default' => true ),
                                              ),
                                        
                                        'filters' =>   
                                        array( 'activity_date_time' => 
                                               array( 'default'    => 'this.month',
                                                      'operatorType' => CRM_Report_Form::OP_DATE),
                                               'subject' => 
                                               array( 'title'      => ts( 'Activity Subject' ),
                                                      'operator'   => 'like' ),
                                               'activity_type_id' => 
                                               array( 'title'   => ts( 'Activity Type' ),
                                                      'operatorType' => CRM_Report_Form::OP_MULTISELECT,
                                                      'options' => CRM_Core_PseudoConstant::activityType(), ), 
                                               ),
                                        'group_bys'=>             
                                        array( 'activity_date_time' => 
                                               array( 'frequency'  => true ),
                                               'activity_type_id'  =>
                                               array( 'title'   => ts( 'Activity Type' )),
                                               'source_contact_id' =>
                                               array( 'default'    => true ),
                                               ),
                                        'grouping'=> 'activity-fields',
                                        'order_bys'=>             
                                        array( 'display_name' => array( 'title' => ts( 'Contact Name' ),
                                                                        'required'  => true ) ),
                                        ),
                                 
                                 'civicrm_activity_assignment'      =>
                                 array( 'dao'     => 'CRM_Activity_DAO_ActivityAssignment',
                                        'fields'  =>
                                        array(
                                              'assignee_contact_id' => array(  'title' => ts( 'Assignee Contact Name' ) ), 
                                              ),
                                        ),
                                 'civicrm_activity_target'      =>
                                 array( 'dao'     => 'CRM_Activity_DAO_ActivityTarget',
                                        'fields'  =>
                                        array(
                                              'target_contact_id' => array(  'title' => ts( 'Target Contact Name' ) ), 
                                              ),
                                        ),
                                 'civicrm_address' =>
                                 array( 'dao' => 'CRM_Core_DAO_Address',
                                        'fields' =>
                                        array( 'street_address'       => null,
                                               //  'city'             => null,
                                               // 'postal_code'       => null,
                                               // 'state_province_id' => array( 'title' => ts( 'State/Province' ) ),
                                               // 'country_id'        => array( 'title' => ts( 'Country' ) ),
                                               ),
                                        'grouping'=> 'contact-fields',
                                        ),

                                 'civicrm_email' => 
                                 array( 'dao' => 'CRM_Core_DAO_Email',
                                        'fields' =>
                                        array( 'email' => array( 'title' => ts( 'Source Contact E-mail' ) ) ),
                                        'grouping'=> 'contact-fields',
                                        ),
                                 
                                 );

        $this->_options = array( 'include_statistics' => array( 'title'  => ts( 'Include Activity Statistics' ),
                                                                'type'   => 'checkbox',
                                                                'default'=> true ),
                                 );
        
        parent::__construct( );
    }

    function select( ) {
        $select = array( );
        $this->_columnHeaders = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            foreach ( $table['fields'] as $fieldName => $field ) {
                if ( CRM_Utils_Array::value( 'required', $field ) ||
                     CRM_Utils_Array::value( $fieldName, $this->_params['fields'] ) ) {
                    if ( $tableName == 'civicrm_address' ) {
                        $this->_addressField = true;

                    } else if ( $tableName == 'civicrm_email' ) {
                        $this->_emailField = true;
                    } else if ( $tableName == 'civicrm_activity_assignment' ) {
                        $this->_assigneeField = true;
                        $this->_columnHeaders["assignee_display_name"]['title'] = $field['title'];
                        $select[] = "assignee.display_name AS assignee_display_name";
                    } else if( $tableName == 'civicrm_activity_target' ) {
                        $this->_targetField = true;
                        $this->_columnHeaders["target_display_name"]['title'] = $field['title'];
                        $select[] = "target.display_name AS target_display_name";
                    } else if ( $tableName == 'civicrm_contact' ) {
                        $this->_columnHeaders["source_display_name"]['title'] = $field['title'];
                        $select[] = "source.display_name AS source_display_name";
                    } else {
                        $select[] = "{$table['alias']}.{$fieldName} as {$tableName}_{$fieldName}";
                    } 
                    
                    if ( CRM_Utils_Array::value( 'no_repeat', $field ) ) {
                        $this->_noRepeats[] = "{$tableName}_{$fieldName}";
                    }
                    
                    
                    $this->_columnHeaders["{$tableName}_{$fieldName}"]['title'] = $field['title'];
                    $this->_columnHeaders["{$tableName}_{$fieldName}"]['type']  = $field['type'];
                }
            }

            if ( array_key_exists('group_bys', $table) ) {
                foreach ( $table['group_bys'] as $fieldName => $field ) {
                    if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                        switch ( $this->_params['group_bys_freq'][$fieldName] ) {
                            
                        case 'YEARWEEK' :
                            $select[] = "DATE_SUB({$field['dbAlias']}, 
INTERVAL WEEKDAY({$field['dbAlias']}) DAY) AS {$tableName}_{$fieldName}_start";

                            $field['title'] = 'Week';
                            break;
                            
                        case 'YEAR' :
                            $select[] = "MAKEDATE(YEAR({$field['dbAlias']}), 1)  
AS {$tableName}_{$fieldName}_start";
                            $field['title'] = 'Year';
                            break;
                            
                        case 'MONTH':
                            $select[] = "DATE_SUB({$field['dbAlias']}, 
INTERVAL (DAYOFMONTH({$field['dbAlias']})-1) DAY) as {$tableName}_{$fieldName}_start";
                            $field['title'] = 'Month';
                            break;
                            
                        case 'QUARTER':
                            $select[] = "STR_TO_DATE(CONCAT( 3 * QUARTER( {$field['dbAlias']} ) -2 , '/', '1', '/', YEAR( {$field['dbAlias']} ) ), '%m/%d/%Y') AS {$tableName}_{$fieldName}_start";
                            $field['title'] = 'Quarter';
                            break;
                            
                        }
                        if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys_freq'] ) ) {
                            $this->_columnHeaders["{$tableName}_{$fieldName}_start"]['title'] = 
                                $field['title'] . ' Beginning';
                            $this->_columnHeaders["{$tableName}_{$fieldName}_start"]['type']  = 
                                $field['type'];
                            $this->_columnHeaders["{$tableName}_{$fieldName}_start"]['group_by'] = 
                                $this->_params['group_bys_freq'][$fieldName];
                        }
                    }
                }
            }
        }

        $this->_select = "SELECT " . implode( ",\n", $select ) . " ";
    }

    function from( ) {
        $this->_from = null;

        $this->_from = "
FROM       civicrm_activity {$this->_aliases['civicrm_activity']}
INNER JOIN civicrm_contact source ON {$this->_aliases['civicrm_activity']}.source_contact_id = source.id
";
        
        /*  if ( $this->_addressField ) {
            $this->_from .= "LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_address']}.contact_id AND {$this->_aliases['civicrm_address']}.is_primary = 1\n";
        }
           
        if ( $this->_emailField ) {
            $this->_from .= "LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']} ON {$this->_aliases['civicrm_contact']}.id = {$this->_aliases['civicrm_email']}.contact_id AND {$this->_aliases['civicrm_email']}.is_primary = 1\n";
        }
        */

        if ( $this->_assigneeField ) {
            $this->_from .= "LEFT JOIN civicrm_activity_assignment {$this->_aliases['civicrm_activity_assignment']} ON {$this->_aliases['civicrm_activity']}.id =  {$this->_aliases['civicrm_activity_assignment']}.activity_id\n";
            $this->_from .= "LEFT JOIN civicrm_contact assignee ON {$this->_aliases['civicrm_activity_assignment']}.assignee_contact_id = assignee.id\n";
        }

        if ( $this->_targetField ) {
            $this->_from .= "LEFT JOIN civicrm_activity_target {$this->_aliases['civicrm_activity_target']} ON {$this->_aliases['civicrm_activity']}.id =  {$this->_aliases['civicrm_activity_target']}.activity_id\n";
            $this->_from .= "LEFT JOIN civicrm_contact target ON {$this->_aliases['civicrm_activity_target']}.target_contact_id = target.id\n";
        }
    }

    function where( ) {
        $clauses = array( );
        foreach ( $this->_columns as $tableName => $table ) {
            if ( array_key_exists('filters', $table) ) {
                foreach ( $table['filters'] as $fieldName => $field ) {
                    $clause = null;
                    if ( $field['type'] & CRM_Utils_Type::T_DATE ) {
                        $relative = CRM_Utils_Array::value( "{$fieldName}_relative", $this->_params );
                        $from     = CRM_Utils_Array::value( "{$fieldName}_from"    , $this->_params );
                        $to       = CRM_Utils_Array::value( "{$fieldName}_to"      , $this->_params );
                        
                        $clause = $this->dateClause( $field['name'], $relative, $from, $to );
                    } else {
                        $op = CRM_Utils_Array::value( "{$fieldName}_op", $this->_params );
                        if ( $op ) {
                            $clause = 
                                $this->whereClause( $field,
                                                    $op,
                                                    CRM_Utils_Array::value( "{$fieldName}_value", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_min", $this->_params ),
                                                    CRM_Utils_Array::value( "{$fieldName}_max", $this->_params ) );
                        }
                    }
                    
                    if ( ! empty( $clause ) ) {
                        $clauses[] = $clause;
                    }
                }
            }
        }

        if ( empty( $clauses ) ) {
            $this->_where = "WHERE ( 1 ) ";
        } else {
            $this->_where = "WHERE " . implode( ' AND ', $clauses );
        }
    }


    function orderBy( ) {
        $this->_orderBy = "";
    }

    function removeDuplicates( &$rows ) {
        if ( empty($this->_noRepeats) ) {
            return;
        }
        $checkList = array();

        foreach ( $rows as $key => $list ) {
            foreach ( $list as $colName => $colVal ) {
                if ( is_array($checkList[$colName]) && 
                     in_array($colVal, $checkList[$colName]) ) {
                    $rows[$key][$colName] = "";
                }
                if ( in_array($colName, $this->_noRepeats) ) {
                    $checkList[$colName][] = $colVal;
                }
            }
        }
    }

    function groupBy( ) {
        $this->_groupBy = "";
        if ( ! empty($this->_params['group_bys']) ) {
            foreach ( $this->_columns as $tableName => $table ) {
                if ( ! empty($table['group_bys']) ) {
                    foreach ( $table['group_bys'] as $fieldName => $field ) {
                        if ( CRM_Utils_Array::value( $fieldName, $this->_params['group_bys'] ) ) {
                            if ( CRM_Utils_Array::value('frequency', $table['group_bys'][$fieldName]) && 
                                 CRM_Utils_Array::value($fieldName, $this->_params['group_bys_freq']) ) {
                                $this->_groupBy[] = 
                                    $this->_params['group_bys_freq'][$fieldName] . "({$field['dbAlias']})";
                            } else {
                                $this->_groupBy[] = $field['dbAlias'];
                            }
                        }
                    }
                }
            }
            $rollUP = "";
            if ( !empty($this->_statFields) && 
                 CRM_Utils_Array::value( 'include_grand_total', $this->_params['options'] ) ) {
                $rollUP = "WITH ROLLUP";
            }
            $this->_groupBy = "GROUP BY " . implode( ', ', $this->_groupBy ) . " $rollUP ";
        }
    }

    function postProcess( ) {
        parent::postProcess();
    }

    function alterDisplay( &$rows ) {
        // custom code to alter rows
        
        $entryFound   = false;
        $activityType = CRM_Core_PseudoConstant::activityType();
        foreach ( $rows as $rowNum => $row ) {
            
            if ( array_key_exists('civicrm_activity_activity_type_id', $row ) ) {
                if ( $value = $row['civicrm_activity_activity_type_id'] ) {
                    $rows[$rowNum]['civicrm_activity_activity_type_id'] = $activityType[$value];
                    $entryFound = true;
                }
            }
            
            if ( !$entryFound ) {
                break;
            }
        }
    }
}
