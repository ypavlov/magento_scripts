<?php
define('MAGENTO', realpath(dirname(__FILE__)));
require_once MAGENTO . '/../app/Mage.php';
Mage::app();

prepareCollection();

function prepareCollection(){
    $resource = Mage::getSingleton('core/resource');
    $connection = $resource->getConnection('core_read');

    //READ eav_attribute_set
    $attribute_sets = readAttributeSets($connection, $resource);

    prepareCsv($attribute_sets, 'attribute_sets.csv');

    //READ eav_attribute_group

    $attribute_groups = readAttributeGroups($attribute_sets, $connection, $resource);

    prepareCsv($attribute_groups, 'attribute_groups.csv');

    //READ eav_entity_attribute

    $entity_attributes = readEntityAttribute($attribute_sets, $attribute_groups, $connection, $resource);

    prepareCsv($entity_attributes, 'entity_attributes.csv');

    //READ eav_attribute

    $attributes = readAttributes($entity_attributes, $connection, $resource);

    prepareCsv($attributes, 'attributes.csv');

    //READ catalog_eav_attribute

    $catalog_eav_attributes = readCatalogEavAttributes($attributes, $connection);

    prepareCsv($catalog_eav_attributes, 'catalog_eav_attribute.csv');
}

/**
 * @param $attributes
 * @param $connection
 * @return mixed
 */
function readCatalogEavAttributes($attributes, $connection)
{
    $attribute_ids = [];
    foreach ($attributes as $attribute) {
        $attribute_ids[] = $attribute['attribute_id'];
    }

    $select_attribute = $connection->select()
        ->from(array('eag' => 'catalog_eav_attribute'))
        ->where('eag.attribute_id IN (' . implode(',', $attribute_ids) . ')')
        ->order('eag.attribute_id ASC');

    $attributes = $connection->fetchAll($select_attribute);
    return $attributes;
}

/**
 * @param $entity_attributes
 * @param $connection
 * @param Mage_Core_Model_Abstract $resource
 * @return mixed
 */
function readAttributes($entity_attributes, $connection, Mage_Core_Model_Abstract $resource)
{
    $attribute_ids = [];
    foreach ($entity_attributes as $entity_attribute) {
        $attribute_ids[] = $entity_attribute['attribute_id'];
    }

    $select_attribute = $connection->select()
        ->from(array('eag' => $resource->getTableName('eav/attribute')))
        ->where('eag.attribute_id IN (' . implode(',', $attribute_ids) . ')')
        ->order('eag.attribute_id ASC');

    $attributes = $connection->fetchAll($select_attribute);
    return $attributes;
}

/**
 * @param array $attribute_sets
 * @param $attribute_groups
 * @param $connection
 * @param Mage_Core_Model_Abstract $resource
 * @return mixed
 */
function readEntityAttribute(array $attribute_sets, $attribute_groups, $connection, Mage_Core_Model_Abstract $resource)
{
    $attribute_set_ids = [];
    $attribute_group_ids = [];
    foreach ($attribute_sets as $attribute_set) {
        $attribute_set_ids[] = $attribute_set['attribute_set_id'];
    }
    foreach ($attribute_groups as $attribute_group) {
        $attribute_group_ids[] = $attribute_group['attribute_group_id'];
    }
    $select_entity_attribute = $connection->select()
        ->from(array('eag' => $resource->getTableName('eav/entity_attribute')))
        ->where('eag.attribute_group_id IN (' . implode(',', $attribute_group_ids) . ')')
        ->order('eag.entity_attribute_id ASC');

    $entity_attributes = $connection->fetchAll($select_entity_attribute);
    return $entity_attributes;
}

/**
 * @param array $attribute_sets
 * @param $connection
 * @param Mage_Core_Model_Abstract $resource
 * @return mixed
 */
function readAttributeGroups(array $attribute_sets, $connection, Mage_Core_Model_Abstract $resource)
{
    $attribute_set_ids = [];
    foreach ($attribute_sets as $attribute_set) {
        $attribute_set_ids[] = $attribute_set['attribute_set_id'];
    }
    $select_attribute_group = $connection->select()
        ->from(array('eag' => $resource->getTableName('eav/attribute_group')))
        ->where('eag.attribute_set_id IN (' . implode(',', $attribute_set_ids) . ')')
        ->order('eag.attribute_group_id ASC');

    $attribute_groups = $connection->fetchAll($select_attribute_group);
    return $attribute_groups;
}

/**
 * @param $connection
 * @param Mage_Core_Model_Abstract $resource
 * @return array
 */
function readAttributeSets($connection, Mage_Core_Model_Abstract $resource)
{
    $select_attribute_set = $connection->select()
        ->from(array('eas' => $resource->getTableName('eav/attribute_set')))
        ->order('eas.attribute_set_id ASC');

    $attribute_sets = $connection->fetchAll($select_attribute_set);

    return $attribute_sets;
}

function prepareCsv($attributesCollection, $filename = "importAttrib.csv", $delimiter = ',', $enclosure = '"'){
    $f = fopen($filename, 'w');
    $first = true;
    foreach ($attributesCollection as $line) {
        if($first){
            $titles = array();
            foreach($line as $field => $val){
                $titles[] = $field;
            }
            fputcsv($f, $titles, $delimiter, $enclosure);
            $first = false;
        }
        fputcsv($f, $line, $delimiter, $enclosure);
    }
}