<?php

/** @var Brideo_CmsPermissions_Model_Setup $installer */
$installer = $this;
$installer->startSetup();

$cmsBlock = Mage::getModel('cms/block')->getCollection();
$cmsBlock->addFieldToFilter('content', array(
    array('like' => '%{{block%'),
    array('like' => '%{{config%')
));
$cmsBlock->addFieldToSelect('content');

$content = $cmsBlock->toXml();

$cmsPage = Mage::getModel('cms/page')->getCollection();
$cmsPage->addFieldToFilter('content', array(
    array('like' => '%{{block%'),
    array('like' => '%{{config%')
));
$cmsPage->addFieldToSelect('content');

$content .= $cmsPage->toXml();

$typeBlockArray = $installer->getVariableBlockArray($content);


if(count($typeBlockArray['block'])) {
    $installer->getConnection()->insertMultiple(
        $installer->getTable('admin/permission_block'),
        $typeBlockArray['block']
    );
}

if(count($typeBlockArray['config'])) {
    $installer->getConnection()->insertMultiple(
        $installer->getTable('admin/permission_variable'),
        $typeBlockArray['config']
    );
}
