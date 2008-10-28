<?php
declare(ENCODING = 'utf-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * The class to generate the controller->action items for the flexform
 *
 * @package thebridge
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @author Jochen Rau <jochen.rau@typoplanet.de>
 */
class tx_thebridge_itemsProcFunc {

	public function user_getAvailabePlugins($config) {
		$extConfArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['thebridge']);
		if ( $extConfArray['pathToPluginsSetup'] != '') {
			$pathToPluginsSetup = $extConfArray['pathToPluginsSetup'];
		} else {
			$pathToPluginsSetup = t3lib_extMgm::extPath('thebridge') . 'Configuration/Plugins.ts';
		}
		$rawSetup = t3lib_div::getURL($pathToPluginsSetup);
		$TSObj = t3lib_div::makeInstance('t3lib_TSparser');
		$TSObj->parse($rawSetup);
		if (is_array($TSObj->setup['plugin.']['tx_thebridge.']['plugins.'])) {
			foreach ($TSObj->setup['plugin.']['tx_thebridge.']['plugins.'] as $plugin => $setup) {
				$config['items'][] = array($setup['label'], substr($plugin,0,-1));
			}
		}
		// $config['items']= array(array( 'FLOW3 Blog (list view)','blog_list'));
		return $config['items'];
	}
}
?>