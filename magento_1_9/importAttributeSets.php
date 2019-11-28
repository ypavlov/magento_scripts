<?php
define('MAGENTO', realpath(dirname(__FILE__)));
require_once MAGENTO . '/../app/Mage.php';
Mage::app();
$entity_type_id = Mage::getModel('catalog/product')->getResource()->getTypeId();

prepareCollection($entity_type_id);

function prepareCollection($ent_type_id){
    $resource = Mage::getSingleton('core/resource');
//    $connection = $resource->getConnection('core_read');
    $write = $resource->getConnection('core_write');



    $write->delete('eav_entity_attribute');
    $write->delete('eav_attribute_group');
    $write->delete('eav_attribute');
    $write->delete('eav_attribute_set');

    $write->delete('catalog_eav_attribute');


//    $write->truncateTable('eav_entity_attribute');
//    $write->truncateTable('eav_attribute_group');
//    $write->truncateTable('eav_attribute');
//    $write->truncateTable('eav_attribute_set');
//
//    $write->truncateTable('catalog_eav_attribute');






    //READ eav_attribute
    $attributes = getCsv('attributes.csv');
    $tableName = 'eav_attribute';
    insertDataToTable($attributes, $write, $tableName);


    //READ catalog_eav_attribute
    $attributes = getCsv('catalog_eav_attribute.csv');
    $tableName = 'catalog_eav_attribute';
    insertDataToTable($attributes, $write, $tableName);


    //READ eav_attribute_set
    $attributes = getCsv('attribute_sets.csv');
    $tableName = 'eav_attribute_set';
    insertDataToTable($attributes, $write, $tableName);

    //READ eav_attribute_group
    $attributes = getCsv('attribute_groups.csv');
    $tableName = 'eav_attribute_group';
    insertDataToTable($attributes, $write, $tableName);


    //READ eav_entity_attribute
    $attributes = getCsv('entity_attributes.csv');
    $tableName = 'eav_entity_attribute';
    insertDataToTable($attributes, $write, $tableName);
}

/**
 * @param array $attributes
 * @param $write
 * @param $tableName
 */
function insertDataToTable(array $attributes, $write, $tableName)
{
    foreach ($attributes as $attribute) {
        if (!empty($attribute)) {
            try {
//                $write->insert($tableName, $attribute);
                $write->insertOnDuplicate($tableName, $attribute, array_keys($attribute));

            } catch (Exception $exception) {
                echo "\n<br />";
                echo($exception->getMessage());
                echo "\n<br />";
                print_r($attribute);
                echo "\n<br />";
            }
        }

    }
}

/**
 * @param $filename
 * @return array
 */
function getCsv($filename){

    $file = fopen($filename,"r");
    if($file === false ) {
        return [];
    }
    while(!feof($file)){
        $csv[] = fgetcsv($file, 0, ',');
    }
    $keys = array_shift($csv);
    foreach ($csv as $i=>$row) {
        $csv[$i] = array_combine($keys, $row);
    }

    return $csv;
}