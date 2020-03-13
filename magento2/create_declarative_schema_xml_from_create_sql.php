<?php 

$sql = "CREATE TABLE `wdevs_branch_locations` (
  `branch_locations_id` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'Store ID',
  `branch_name` varchar(255) NOT NULL COMMENT 'Branch Name',
  `branch_code` varchar(255) NOT NULL COMMENT 'Branch code',
	`warehouse_code` varchar(255) NOT NULL COMMENT 'warehouse code',
	`address` varchar(255) NOT NULL COMMENT 'address',
	`city` varchar(255) NOT NULL COMMENT 'city',
	`state` varchar(255) NOT NULL COMMENT 'state',
	`zip` varchar(255) NOT NULL COMMENT 'zip',
	`phone` varchar(255) NOT NULL COMMENT 'phone',
	`email` varchar(255) NOT NULL COMMENT 'email',
	`operation_hours` varchar(255) NOT NULL COMMENT 'hours of operation',
    `location` POINT null,  
	`latitude` varchar(25) NOT NULL COMMENT 'latitude',
	`longitude` varchar(25) NOT NULL COMMENT 'longitude',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Creation Time',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update Time',
  PRIMARY KEY (`branch_locations_id`),
  SPATIAL INDEX `SPATIAL` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Branch Locations'; ";



$re = '/CREATE\s+TABLE\s+`(.*?)`\s+\((.*)\).*?;/is';

preg_match_all($re, $sql, $matches, PREG_SET_ORDER, 0);

$tableName = $matches[0][1];

$tableBody = $matches[0][2];

//echo($matches[0][2]);
$lines = explode(',', $tableBody);

$xmlBody = '';
foreach ($lines as $line ) {

    $re2 = '/`(.*?)`\s+(.*?)\s+.*?COMMENT \'(.*?)\'/is';

    preg_match_all($re2, trim($line), $matches2, PREG_SET_ORDER, 0);

  //  var_dump($matches2);
    
    $fieldName = $matches2[0][1];
    $fieldType = $matches2[0][2];
    $fieldComment = $matches2[0][3];

    $re3 = '/(.*?)\((\d+)\)/is';

    preg_match_all($re3, trim($fieldType), $matches3, PREG_SET_ORDER, 0);
    
    $fieldTypeName = $matches3[0][1];
    $fieldTypeSize = $matches3[0][2];

    $xmlBody .="    ";
    if($fieldTypeName == 'varchar') {
        $xmlBody .= '<column xsi:type="'.$fieldTypeName.'" name="'.$fieldName.'" length="'.$fieldTypeSize.'" nullable="false"
                 comment="'.$fieldComment.'"/>'. "\n";
    } else {
        $xmlBody .= '<column xsi:type="'.$fieldTypeName.'" name="'.$fieldName.'" padding="'.$fieldTypeSize.'" unsigned="true" nullable="false"
                identity="true" comment="'.$fieldComment.'"/>'. "\n";
    }

    

}


$xml = '<table name="'.$tableName.'" resource="default" engine="innodb" comment="'.$tableName.'">'."\n";
$xml .= $xmlBody;
$xml .= "</table>\n\n";


echo $xml;
/*


<table name="wdevs_branch_groups" resource="default" engine="innodb" comment="Branch Groups">
        <column xsi:type="smallint" name="branch_group_id" padding="5" unsigned="true" nullable="false"
                identity="true" comment="Entity ID"/>
        <column xsi:type="varchar" name="group_name" nullable="false" length="255" comment="Group Name" />
        <column xsi:type="timestamp" name="created_at" on_update="false" nullable="false" default="CURRENT_TIMESTAMP"
                comment="Creation Time"/>
        <column xsi:type="timestamp" name="updated_at" on_update="true" nullable="false" default="CURRENT_TIMESTAMP"
                comment="Update Time"/>
        <constraint xsi:type="primary" referenceId="PRIMARY">
            <column name="branch_group_id"/>
        </constraint>
        <index referenceId="BRANCH_GROUP_INDEXERS" indexType="fulltext">
            <column name="group_name"/>
        </index>
    </table>
    
*/    





































