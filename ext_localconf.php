<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc'][] = 'EXT:thebridge/Classes/class.tx_thebridge_router.php:&tx_thebridge_router->decodeRoute';
?>