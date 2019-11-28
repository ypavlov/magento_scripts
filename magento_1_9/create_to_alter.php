<?php

$handle = fopen("dump_no-data.sql", "r");

$reCREATE_TABLE = '/^CREATE TABLE `(\w+)` \(/m';

$reCONSTRAINT = '/^CONSTRAINT .*$/m';

$tableName = '';
$keys = [];


while (($line = fgets($handle)) !== false) {
    // process the line read.
    if(preg_match_all($reCONSTRAINT, trim($line), $matches, PREG_SET_ORDER, 0)) {
        $keys[] = " ADD " . $line;
    }
    if(preg_match_all($reCREATE_TABLE, trim($line), $matches, PREG_SET_ORDER, 0)) {
//        var_dump($matches[0][1]); die;

        if(!empty($keys)) {

            $alter = "ALTER TABLE `" . $tableName . "` ".
            implode(",", $keys);

//            "
//ADD CONSTRAINT `FK_EAV_ENTITY_STORE_STORE_ID_CORE_STORE_STORE_ID`
//  FOREIGN KEY (`store_id`)
//  REFERENCES `york_s`.`core_store` (`store_id`)
//  ON DELETE CASCADE
//  ON UPDATE CASCADE;"

            echo $alter . "\n\n";
        }

        $keys = [];
        $tableName = $matches[0][1];
    }
}
