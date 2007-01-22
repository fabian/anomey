<?php

/*
 * anomey 2.1 - content management
 * ================================
 * 
 * Copyright (C) 2006 - Adrian Egloff (adrian@anomey.ch), 
 * Cyril Gabathuler (cyril@anomey.ch) and Fabian Vogler (fabian@anomey.ch)
 * 
 * This file is part of anomey. For more information about anomey
 * visit http://anomey.ch/ or write a mail to info@anomey.ch.
 * 
 * anomey is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * anomey is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with anomey (license.txt); if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * or visit http://www.gnu.org/copyleft/gpl.html.
 */

require_once 'util.package.php';
require_once 'site.package.php';

class ActionNotFoundException extends Exception {
}

class LinkNotFoundException extends Exception {
}

class AnomeyTextParser implements TextParser {
	
	private $processor;
	
	public function __construct(Processor $processor) {
		$this->processor = $processor;
	}
	
	public function parse(Model $model, $text) {
		return $this->processor->resolveURIs($model, $text);
	}
}

/**
 * A class which can contain links.
 * 
 * @author Fabian Vogler
 */
class LinkContainer extends Bean {

	private $links = array ();

	public function getLinks() {
		return $this->links;
	}

	public function getLink($key) {
		if (isset ($this->links[$key])) {
			return $this->links[$key];
		} else {
			throw new LinkNotFoundException('Link with key "' . $key . '" not found.');
		}
	}

	public function add($name, Link $link) {
		if (!isset ($this->links[$name])) {
			$this->links[$name] = $link;
		}
	}
}

/**
 * Procsessing class of anomey.
 * 
 * @author Fabian Vogler
 */
class Processor extends LinkContainer {

	/**
	 * Name of the configuration xml file.
	 */
	private $configuration;

	/**
	 * Security object.
	 */
	private $security;

	/**
	 * Trail to the home page.
	 */
	private $homeTrail = '/home';

	public function getHomeTrail() {
		return $this->homeTrail;
	}

	public function setHomeTrail($homeTrail) {
		$this->homeTrail = $homeTrail;
	}

	/**
	 * Trail to the access denied page.
	 */
	private $accessDeniedTrail = '';

	public function getAccessDeniedTrail() {
		return $this->accessDeniedTrail;
	}

	public function setAccessDeniedTrail($accessDeniedTrail) {
		$this->accessDeniedTrail = $accessDeniedTrail;
	}

	/**
	 * Trail to the page not found page.
	 */
	private $pageNotFoundTrail = '';

	public function getPageNotFoundTrail() {
		return $this->pageNotFoundTrail;
	}

	public function setPageNotFoundTrail($pageNotFoundTrail) {
		$this->pageNotFoundTrail = $pageNotFoundTrail;
	}

	/**
	 * URL of the current processing.
	 */
	private $url;

	public function getURL() {
		return $this->url;
	}

	/**
	 * Site of the current processing.
	 */
	private $site;

	private $extensions = array ();

	public function __construct(URL $url, $configuration, Security $security, Site $site) {
		$this->url = $url;
		$this->configuration = $configuration;
		$this->security = $security;
		$this->site = $site;
		
		$site->addTextParser(new AnomeyTextParser($this));
		
		try {
			$xml = Xml :: load($this->configuration);
	
			$this->homeTrail = (string) $xml->specialTrails->home;
			$this->accessDeniedTrail = (string) $xml->specialTrails->accessDenied;
			$this->pageNotFoundTrail = (string) $xml->specialTrails->pageNotFound;
		} catch (FileNotFoundException $e) {
			
		}

		$this->add('login', new SiteLink($site, 'Login', true, 'LoginAction'));
		$this->add('logout', new SiteLink($site, 'Logout', true, 'LogoutAction'));
		$this->add('trusted', new SiteLink($site, 'Enter password', true, 'TrustedAction'));

		$paths = array (
			'core/anomey/extensions',
			$security->getProfile() . '/extensions'
		);

		// load module links
		$this->createIndex($this, $site);

		// load extensions
		foreach($security->getExtensions() as $extension) {
			if($extension instanceof ActionContainer) {
				$actions = call_user_func(array (
					get_class($extension),
					'getActions'
				));
					
				foreach($actions as $name => $actionClass) {
					$trail = '/' . $name;
					$link = new SecureLink($site, '', $trail, true, $actionClass);
					self::parseAction($site, $link);
					$this->add($name, $link);
				}
			}
			
			$extension->setSite($site);
			$extension->setProcessor($this);
			$extension->load();
		}
	}

	public function save() {
		$xml = XML :: create('site');
		$specialTrails = $xml->addChild('specialTrails');
		$specialTrails->addChild('home', $this->getHomeTrail());
		$specialTrails->addChild('accessDenied', $this->getAccessDeniedTrail());
		$specialTrails->addChild('pageNotFound', $this->getPageNotFoundTrail());

		$xml->save($this->configuration);
	}

	public function execute(Request $request) {
		header('X-Powered-By: anomey/' . Anomey :: VERSION);

		if ($request->getTrail() == '/') {
			$request->setTrail($this->getHomeTrail());
		}

		foreach ($this->security->getExtensions() as $extension) {
			$extension->invoke($request);
		}

		try {
			$this->executeAction($request, $request->getTrail());
		} catch (ActionNotFoundException $eee) {
			try {
				$this->executeAction($request, $this->getPageNotFoundTrail());
			} catch (ActionNotFoundException $ee) {
				exit ('Could not find the error action, defined in "' . $this->configuration . '"!');
			} catch (AccessDeniedException $ee) {
				exit ('Cannot access the error action, defined in "' . $this->configuration . '"!');
			}
		} catch (AccessDeniedException $e) {
			if ($request->getUser() == null) {
				$this->executeAction($request, '/login');
			} else {
				try {
					$this->executeAction($request, $this->getAccessDeniedTrail());
				} catch (ActionNotFoundException $ee) {
					exit ('Could not find the access-denied trail, defined in "' . $this->configuration . '"!');
				} catch (AccessDeniedException $ee) {
					exit ('Cannot access the access-denied trail, defined in "' . $this->configuration . '"!');
				}
			}
		} catch (TrustedUserRequiredException $e) {
			$this->executeAction($request, '/trusted');
		}
	}

	private function executeAction($request, $trail) {
		try {
			$link = $this->findLink($this, $trail);
		} catch (LinkNotFoundException $e) {
			throw new ActionNotFoundException($e->getMessage());
		}
		
		$link->execute($this, $request, $this->security);
	}

	public function refresh($request) {
		$this->forward($request, $request->getTrail(), $request->getMessages());
	}

	public function forward(Request $request, $trail, $messages = array (), $query = '', $fragment = '') {
		$url = $this->makeURL($trail, $query . SID, $fragment);

		$this->redirect($request, $url, $messages);
	}
	
	/**
	 * Redirects to an URL.
	 */
	public function redirect(Request $request, $url, $messages = array ()) {
		$session = $request->getSession();
		$session->store('systemMessages', $messages);
		$session->commit();

		header('X-Powered-By: anomey/' . Anomey :: VERSION);
		header('Location: ' . $url, true, 301);
	}

	public function makeURL($trail, $query = '', $fragment = '') {
		$url = $this->url->getServer() . $this->makeRelativeURL($trail, $query, $fragment);

		return $url;
	}

	public function makeRelativeURL($trail, $query = '', $fragment = '') {
		if ($trail == $this->getHomeTrail()) {
			$trail = '/';
		}
		
		$url = $this->url->getPath();
		if ($trail != '/') {
			$url .= $this->url->getBase() . substr($trail, 1);
		}

		if ($query != '') {
			$url .= '?' . $query;
		}

		if ($fragment != '') {
			$url .= '#' . $fragment;
		}

		return $url;
	}

	public static function parseAction(Model $model, SecureLink $parent) {
		$reflection = new ReflectionClass($parent->getAction());
		if ($reflection->implementsInterface('ProtectedAction')) {
			$parent->setRequiredPermissions(call_user_func(array (
				$parent->getAction(),
				'getRequiredPermissions'
			)));
		}
		
		if ($reflection->implementsInterface('SecureAction')) {
			$parent->setSecurity('high');
		}
		
		if ($reflection->implementsInterface('ActionContainer')) {
			$actions = call_user_func(array (
				$parent->getAction(),
				'getActions'
			));
			foreach($actions as $name => $actionClass) {
				$newTrail = $parent->getTrail() . '/' . $name;
				$link = new SecureLink($model, '', $newTrail, true, $actionClass);
				self::parseAction($model, $link);
				$parent->add($name, $link);
			}
		}
	}

	private function createIndex(LinkContainer $parentLink, Model $model) {
		foreach ($model->getChilds() as $name => $module) {
			$actionClass = get_class($module) . 'Action';
			$link = new SecureLink($module, $module->getTitle(), $module->getPath(), $module->getHide(), $actionClass);
			self::parseAction($module, $link);
			
			$parentLink->add($name, $link);
			$this->createIndex($link, $module);
		}
	}

	public static function readSecurity(SimpleXMLElement $element) {
		if (isset ($element['security'])) {
			return (string) $element['security'];
		} else {
			return 'normal';
		}
	}

	/**
	 * @param LinkContainer $link
	 * @param string $trail
	 */
	public static function findLink(LinkContainer $link, $trail) {

		$pathArray = explode('/', $trail);

		$name = Value :: get($pathArray[1]);

		$currentLink = $link->getLink($name);

		try {
			unset ($pathArray[1]); // Delete the current link
			$link = self :: findLink($currentLink, implode('/', $pathArray));
		} catch (LinkNotFoundException $e) {
			$link = $currentLink;
		}

		return $link;
	}

	public function getLinkByTrail($trail) {
		return $this->findLink($this, $trail);
	}
	
	public function resolveURIs(Model $model, $string) {
		$string = preg_replace('/media:('.URI::CHARS.'*)/e', '$this->getURL()->getPath().$this->security->getProfile()."/media/".$model->getId()."/\\1"', $string);
		$string = preg_replace('/page:('.URI::CHARS.'*)/e', '$this->makeRelativeURL($this->resolveTrail($model->getPath(), "\\1"))', $string);
		return $string;
	}

	/**
	 * Transforms a relative trail inside a model
	 * into a absolute.
	 */
	public static function resolveTrail($base, $trail) {
		if (substr($trail, 0, 1) != '/') {
			$newTrail = $base;

			if ($trail != '' and $newTrail != '/') {
				$newTrail .= '/';
			}

			$trail = $newTrail . $trail;
		}

		return $trail;
	}
}

/**
 * Executable Link class.
 */
abstract class Link extends LinkContainer {

	private $title;

	public function getTitle() {
		return $this->title;
	}

	private $trail;

	public function getTrail() {
		return $this->trail;
	}

	private $hide;

	public function getHide() {
		return $this->hide;
	}

	function __construct($title, $trail, $hide) {
		$this->title = $title;
		$this->trail = $trail;
		$this->hide = $hide;
	}

	abstract public function execute(Processor $processor, Request $request, Security $security);

	abstract public function isExecutable($user);
}

class SecureLink extends Link {

	private $model;

	public function getModel() {
		return $this->model;
	}

	public function isExecutable($user) {
		return $this->getModel()->checkPermissions($user, $this->getRequiredPermissions());
	}

	private $requiredPermissions = array ();

	public function getRequiredPermissions() {
		return $this->requiredPermissions;
	}

	public function addRequiredPermission($permission) {
		$this->requiredPermissions[] = $permission;
	}

	public function setRequiredPermissions($permissions) {
		$this->requiredPermissions = $permissions;
	}

	private $security = 'normal';

	public function getSecurity() {
		return $this->security;
	}

	public function setSecurity($security) {
		$this->security = $security;
	}

	private $action;

	public function getAction() {
		return $this->action;
	}

	public function __construct(Model $model, $title, $trail, $hide, $action) {
		parent :: __construct($title, $trail, $hide);
		$this->model = $model;
		$this->action = $action;
	}

	public function execute(Processor $processor, Request $request, Security $security) {
		$nick = $request->getUser() == null ? '' : $request->getUser()->getNick();
		if (!$this->isExecutable($request->getUser())) {
			throw new AccessDeniedException('User ' . $nick . ' can\'t access this page!');
		}
		elseif ($this->getSecurity() == 'high' && !$request->getUser()->getTrusted()) {
			throw new TrustedUserRequiredException('User ' . $nick . ' needs to be trusted to access this page!');
		}
		$trail = explode('/', substr($request->getTrail(), strlen($this->getTrail())));
		$request->addParameters($trail);

		// load model data
		$this->getModel()->load();
		
		$action = new $this->action($processor, $request, $this->getModel(), $security);
		header('Content-type: ' . $action->getContentType());
		$action->execute();
	}

}

/**
 * Link which checks the site permissions instead of
 * the model permissions.
 */
class SecureSiteLink extends SecureLink {
	public function isExecutable($user) {
		return $this->getModel()->getSite()->checkPermissions($user, $this->getRequiredPermissions());
	}
}

/**
 * Link which points to an action without a module.
 */
class SiteLink extends Link {
	private $site;
	private $action;

	public function __construct(Site $site, $title, $hide, $action) {
		parent :: __construct($title, '/', $hide);
		$this->site = $site;
		$this->action = $action;
	}

	public function execute(Processor $processor, Request $request, Security $security) {
		$action = new $this->action($processor, $request, $this->site, $security);
		header('Content-type: ' . $action->getContentType());
		$action->execute();
	}

	public function isExecutable($user) {
		return true;
	}
}

class Request extends Bean {

	private $user;

	public function getUser() {
		return $this->user;
	}

	private $method;

	public function getMethod() {
		return $this->method;
	}

	private $trail;

	public function getTrail() {
		return $this->trail;
	}

	public function setTrail($trail) {
		$this->trail = $trail;
	}

	private $session;

	public function getSession() {
		return $this->session;
	}

	private $cookie;

	public function getCookie() {
		return $this->cookie;
	}

	private $parameters;

	public function getParameters() {
		return $this->parameters;
	}

	public function getParameter($name, $default = '') {
		return Value :: get($this->parameters[$name], $default);
	}

	public function addParameters($parameters) {
		$this->parameters = array_merge($this->parameters, $parameters);
	}

	private $messages;

	public function getMessages() {
		$this->flushMessages();
		return $this->messages;
	}

	private function flushMessages() {
		foreach ($this->messages as $key => $value) {
			if ($value->isDisplayed()) {
				unset ($this->messages[$key]);
			}
		}
	}

	public function addMessage($message) {
		$this->messages[] = $message;
	}

	public function addMessages($messages) {
		$this->messages = array_merge($this->messages, $messages);
	}

	public function __construct($user, $method, $trail, Session $session, Cookie $cookie, $parameters = array (), $messages = array ()) {
		$this->user = $user;
		$this->method = $method;
		$this->trail = $trail;
		$this->session = $session;
		$this->cookie = $cookie;
		$this->parameters = $parameters;
		$this->messages = $messages;
	}
}

class Extension {

	private $site;

	public function getSite() {
		return $this->site;
	}
	
	public function setSite(Site $site) {
		$this->site = $site;
	}

	private $processor;

	public function getProcessor() {
		return $this->processor;
	}
	
	public function setProcessor(Processor $processor) {
		$this->processor = $processor;
	}

	/**
	 * To be overloaded.
	 */
	public function load() {

	}

	/**
	 * To be overloaded.
	 */
	public function invoke(Request $request) {

	}
}
?>
