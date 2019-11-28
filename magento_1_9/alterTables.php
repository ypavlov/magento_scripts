<?php
define('MAGENTO', realpath(dirname(__FILE__)));
require_once MAGENTO . '/../app/Mage.php';
Mage::app();

prepareCollection();

function prepareCollection(){
    $resource = Mage::getSingleton('core/resource');
//    $connection = $resource->getConnection('core_read');
    $write = $resource->getConnection('core_write');


    $filecontent = file_get_contents('clear.sql');

    $alters = explode(';', $filecontent);

    $success = 0;

echo "<pre>";

//    var_dump(trim($alters[0]));die;

    foreach ($alters as $alter) {
        try {
            $write->query($alter);
            $success++;
        } catch (Exception $exception){
            $errorMessage =  $exception->getMessage();

            $reDup = '/duplicate key in table/m';
            if(preg_match_all($reDup, $errorMessage, $matchesDup, PREG_SET_ORDER, 0)) {
                continue;
            }

            echo "++++\n\n";
            echo  $errorMessage;
            echo "++++\n\n";
            $reAlter = '/ALTER TABLE `(\w+)` /m';
            $reError = '/CONSTRAINT `\w+` FOREIGN KEY \(`(\w+)`\) REFERENCES `(\w+)` \(`(\w+)`\) ON D/m';

            if( preg_match_all($reError, $errorMessage, $matches, PREG_SET_ORDER, 0)
                && preg_match_all($reAlter, $alter, $matchesAlter, PREG_SET_ORDER, 0)) {
                $tableField = $matches[0][1];
                $tableName =  $matches[0][2];
                $tableIndex = $matches[0][3];

                $alterTableName = $matchesAlter[0][1];

                $sql = "SELECT 
                            *
                        FROM
                            " . $alterTableName . "
                        WHERE
                            " . $tableField . " NOT IN (SELECT 
                                    " . $tableIndex . "
                                FROM
                                    " . $tableName . ")
                ";

                $deleteSql = "DELETE
                        FROM
                            " . $alterTableName . "
                        WHERE
                            " . $tableField . " NOT IN (SELECT 
                                    " . $tableIndex . "
                                FROM
                                    " . $tableName . ")
                ";

                try {
                    $write->query($deleteSql);
                } catch (Exception $exception2) {
                    var_dump($exception2->getMessage());
                }

                echo "----\n\n";
                echo $sql;
                echo "----\n\n";

            }

//            echo $alter . "<br >\n\n";
        }
    }

    var_dump($success);

}


