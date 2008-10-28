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

require_once (PATH_t3lib.'class.t3lib_tsparser.php');

/**
 * The router tries to determine the PID of the plugin by decoding the route submitted by FLOW3.
 *
 * @package thebridge
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @author Jochen Rau <jochen.rau@typoplanet.de>
 */
class tx_thebridge_router {

	/**
	 * This method sets the appropriate PIDs inside TYPO3 V4 for URIs rendered by FLOW3
	 *
	 * @return string 
	 * @author Jochen Rau <jochen.rau@typoplanet.de>
	 **/
	public function decodeRoute($params) {
		$pObj = &$params['pObj'];
		if ($pObj->siteScript && substr($pObj->siteScript, 0, 9) != 'index.php' && substr($pObj->siteScript, 0, 1) != '?') {
			$extConfArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['thebridge']);
			if ( $extConfArray['pathToPluginsSetup'] != '') {
				$pathToPluginsSetup = $extConfArray['pathToPluginsSetup'];
			} else {
				$pathToPluginsSetup = t3lib_extMgm::extPath('thebridge') . 'Configuration/Setup/setup.txt';
			}
			$rawSetup = t3lib_div::getURL($pathToPluginsSetup);
			$TSObj = t3lib_div::makeInstance('t3lib_TSparser');
			$TSObj->parse($rawSetup);
			$matched = FALSE;
			if (is_array($TSObj->setup['plugin.']['tx_thebridge.']['plugins.'])) {
				foreach ($TSObj->setup['plugin.']['tx_thebridge.']['plugins.'] as $plugin => $setup) {
					if (is_array($setup['patterns.'])) {
						foreach ($setup['patterns.'] as $pattern) {
							if (preg_match('#' . $pattern . '#i', $pObj->siteScript) > 0) {
								$pObj->id = $setup['pid'];
								$matched = TRUE;
								break;
							}
						}
					}
					if ($matched) break;
				}
			}
			// TODO Implement 'best guess' if nothing is found by reading tt_content table
			$pObj->type = isset($TSObj->setup['plugin.']['tx_thebridge.']['type']) ? $TSObj->setup['plugin.']['tx_thebridge.']['type'] : NULL;
		}
	}
}	
?>