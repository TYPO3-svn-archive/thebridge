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
define('FLOW3_PATH_PUBLIC', PATH_site);

/**
 * The bridge to the core hyper FLOW3 class
 *
 * @package thebridge
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 * @author Jochen Rau <jochen.rau@typoplanet.de>
 */
class tx_thebridge_dispatcher {
	
	/**
	 * An instance of FLOW3 framework
	 *
	 * @var F3::FLOW3
	 **/
	protected $framework;

	/**
	 * A reference to the component factory
	 *
	 * @var F3::FLOW3::Component::FactoryInterface
	 */
	protected $componentFactory;

	/**
	 * This method is called by the TYPO3 v4 Framework. It initializes the FLOW3 framework
	 * and passes appropriate request and response objects.
	 *
	 * @return string The content rendered by FLOW3
	 * @author Jochen Rau <jochen.rau@typoplanet.de>
	 **/
	public function dispatch($content, $setup) {
		$extConfArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['thebridge']);
		require_once(realpath(PATH_site . '../') . '/' . $extConfArray['pathToFlow3']);

		if ($GLOBALS['FLOW3'] instanceof F3::FLOW3) {
			$this->framework = $GLOBALS['FLOW3'];
		} else {
			$context = isset($setup['context']) ? $setup['context'] : NULL;				
			// TODO To backup the environment was necessary after rev#1384 of FLOW3. This may break if a package accesses a SuperGlobalReplacement.
			$environment = array();
			$environment['GET'] = $_GET;
			$environment['POST'] = $_POST;
			$environment['SERVER'] = $_SERVER;
			$this->framework = new F3::FLOW3($context);
			$this->framework->initialize();
			$GLOBALS['FLOW3'] = $this->framework;
			$_GET = $environment['GET'];
			$_POST = $environment['POST'];
			$_SERVER = $environment['SERVER'];
		}
		if (!$this->framework instanceof F3::FLOW3) throw new Exception('FLOW3 could not be initialized. Maybe you have to set the correct path in the Extension Manager first. The current path (relative to your web pages root folder) is "' . $extConfArray['pathToFlow3'] . '"');


		$this->componentFactory = $this->framework->getComponentManager()->getComponentFactory();		
		$request = $this->componentFactory->getComponent('F3::FLOW3::MVC::Web::RequestBuilder')->build();
		$request->setArgument('content', $content);
		if (!$GLOBALS['TSFE']->siteScript || substr($GLOBALS['TSFE']->siteScript, 0, 9) == 'index.php' || substr($GLOBALS['TSFE']->siteScript, 0, 1) == '?') {
			if (!is_array($this->cObj->data['pi_flexform']) && $this->cObj->data['pi_flexform'])	{
				$this->cObj->data['pi_flexform'] = t3lib_div::xml2array($this->cObj->data['pi_flexform']);
				if (!is_array($this->cObj->data['pi_flexform'])) $this->cObj->data['pi_flexform'] = array();
			}
			$plugin = $this->cObj->data['pi_flexform']['data']['sDEF']['lDEF']['plugin']['vDEF'];
			$controllerComponentNamePattern = isset($setup['plugins.'][$plugin . '.']['controllerComponentNamePattern']) ? $setup['plugins.'][$plugin . '.']['controllerComponentNamePattern'] : NULL;
			$packageKey = isset($setup['plugins.'][$plugin . '.']['package']) ? $setup['plugins.'][$plugin . '.']['package'] : NULL;
			$controllerName = isset($setup['plugins.'][$plugin . '.']['controller']) ? $setup['plugins.'][$plugin . '.']['controller'] : NULL;
			$actionName = isset($setup['plugins.'][$plugin . '.']['action']) ? $setup['plugins.'][$plugin . '.']['action'] : NULL;
			if ($controllerComponentNamePattern !== NULL) $request->setControllerComponentNamePattern($controllerComponentNamePattern);
			if ($packageKey !== NULL && $controllerName !== NULL) {
				$request->setControllerPackageKey($packageKey);
				$request->setControllerName($controllerName);
			}
			if ($actionName !== NULL) $request->setControllerActionName($actionName);
		}
		$router = $this->componentFactory->getComponent('F3::FLOW3::MVC::Web::Routing::Router');
		$router->route($request);
		
		$response = $this->componentFactory->getComponent('F3::FLOW3::MVC::Web::Response');
		$response->setContent($input);
		
		// try-catch is required to enable easy redirects and forwardings by the controller classes inside packages
		try {
			$controller = $this->componentFactory->getComponent($request->getControllerComponentName());
			$controller->processRequest($request, $response);
		} catch (F3::FLOW3::MVC::Exception::StopAction $ignoredException) {
		}
		
		$this->componentFactory->getComponent('F3::FLOW3::Persistence::Manager')->persistAll();
		
		return $response->getContent();
	}
}	
?>