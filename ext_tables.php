<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');
t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/Setup', 'Setup');
t3lib_extMgm::addPlugin(array('FLOW3 Generic Plugin', $_EXTKEY), 'list_type');

// TODO enable flexform configuration
include_once(t3lib_extMgm::extPath($_EXTKEY) . 'Classes/class.tx_thebridge_itemsProcFunc.php');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY] = 'pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY, 'FILE:EXT:thebridge/Resources/Flexforms/flexform_ds.xml');
?>