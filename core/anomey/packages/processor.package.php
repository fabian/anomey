<?php

/*
 * anomey 2.1 - content management
 * ================================
 * 
 * Copyright © 2006, 2007 - Adrian Egloff <adrian@anomey.ch>, 
 * Cyril Gabathuler <cyril@anomey.ch> and Fabian Vogler <fabian@anomey.ch>
 * 
 * This file is part of anomey. For more information about anomey
 * visit <http://anomey.ch/> or write a mail to <info@anomey.ch>.
 * 
 * anomey is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * anomey is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with anomey (license.txt). If not, see <http://www.gnu.org/licenses/>.
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

	public function getLink($name) {
		if($name) {
			foreach(array_keys($this->links) as $pattern) {
				if(preg_match('/^' . $pattern . '$/i', $name)) {
					return $this->links[$pattern];
				}
			}
		}
		throw new LinkNotFoundException('Link with pattern "' . $name . '" not found.');
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
	private $accessDeniedTrail = '/access-denied';

	public function getAccessDeniedTrail() {
		return $this->accessDeniedTrail;
	}

	public function setAccessDeniedTrail($accessDeniedTrail) {
		$this->accessDeniedTrail = $accessDeniedTrail;
	}

	/**
	 * Trail to the page not found page.
	 */
	private $pageNotFoundTrail = '/not-found';

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
		$this->add('update', new SiteLink($site, 'Update', true, 'UpdateAction'));

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
					$link = new SecureLink($site, '', true, $actionClass);
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

	public function execute(Request $request, Response $response) {
		header('X-Powered-By: anomey/' . Anomey :: VERSION);

		if ($request->getTrail() == '/') {
			$request->setTrail($this->getHomeTrail());
		}

		foreach ($this->security->getExtensions() as $extension) {
			$extension->invoke($request);
		}

		try {
			$this->executeAction($request, $response, $request->getTrail());
		} catch (ActionNotFoundException $eee) {
			try {
				$this->executeAction($request, $response, $this->getPageNotFoundTrail());
			} catch (ActionNotFoundException $ee) {
				throw new Exception('Found neither the requested page "' . htmlentities($request->getTrail()) . '" nor the page not found "' . 
					htmlentities($this->getPageNotFoundTrail()) . '", defined in "' . $this->configuration . '"! ' . 
					'Please go to the <a href="' . $this->makeRelativeURL('/admin/pages/') . '">pages admin</a> and create this pages or ' . 
					'define existing pages for "Home page" and "Page not found" in the ' . 
					'<a href="' . $this->makeRelativeURL('/admin/settings/') . '">settings admin</a>.');
			} catch (AccessDeniedException $ee) {
				throw new Exception('Can neither find the requested page "' . htmlentities($request->getTrail()) . '" nor access the page ' . 
					'not found "' . htmlentities($this->getPageNotFoundTrail()) . '", defined in "' . $this->configuration . '"! Please go to the ' . 
					'<a href="' . $this->makeRelativeURL('/admin/settings/') . '">settings admin</a> and set "Page not found"" to a page which is ' . 
					'accessible for everyone.');
			}
		} catch (AccessDeniedException $e) {
			if ($request->getUser() == null) {
				$this->executeAction($request, $response, '/login');
			} else {
				try {
					$this->executeAction($request, $response, $this->getAccessDeniedTrail());
				} catch (ActionNotFoundException $ee) {
					throw new Exception('Could neither find the requested page "' . htmlentities($request->getTrail()) . '" nor access the ' . 
						'access denied page "' . htmlentities($this->getAccessDeniedTrail()) . '", defined in "' . $this->configuration . '"! ' . 
						'Please go to the <a href="' . $this->makeRelativeURL('/admin/pages/') . '">pages admin</a> and create this pages or ' . 
						'define existing pages for "Home page" and "Access denied" in the ' . 
						'<a href="' . $this->makeRelativeURL('/admin/settings/') . '">settings admin</a>.');
				} catch (AccessDeniedException $ee) {
					throw new Exception('Could neither find the requested page "' . htmlentities($request->getTrail()) . '" nor access the ' . 
						'access denied page "' . htmlentities($this->getAccessDeniedTrail()) . '", defined in "' . $this->configuration . '"! ' . 
						'Please go to the <a href="' . $this->makeRelativeURL('/admin/settings/') . '">settings admin</a> and set "Access denied" ' . 
						' to a page which is accessible for everyone');
				}
			}
		} catch (TrustedUserRequiredException $e) {
			$this->executeAction($request, $response, '/trusted');
		}

		foreach ($this->security->getExtensions() as $extension) {
			$extension->finish($response);
		}
	}

	private function executeAction(Request $request, Response $response, $trail) {
		$this->callAction($this, $request, $response, $trail, $this->security);
	}
	
	public function callAction(LinkContainer $container, Request $request, Response $response, $trail, $security) {
		try {
			$link = $this->findLink($container, $trail);
		} catch (LinkNotFoundException $e) {
			throw new ActionNotFoundException($e->getMessage());
		}
		
		$link->execute($this, $request, $response, $security);
	}

	public function refresh(Request $request, Response $response) {
		$this->forward($request, $response, $request->getTrail(), $request->getMessages());
	}

	public function forward(Request $request, Response $response, $trail, $messages = array (), $query = '', $fragment = '') {
		$url = $this->makeURL($trail, $query . SID, $fragment);

		$this->redirect($request, $response, $url, $messages);
	}
	
	/**
	 * Redirects to an URL.
	 */
	public function redirect(Request $request, Response $response, $url, $messages = array ()) {
		$session = $request->getSession();
		$session->store('systemMessages', $messages);
		$session->commit();

		$response->setHeader('X-Powered-By', 'anomey/' . Anomey :: VERSION);
		$response->setHeader('Location', $url);
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
				$link = new SecureLink($model, '', true, $actionClass);
				self::parseAction($model, $link);
				$parent->add($name, $link);
			}
		}
	}

	private function createIndex(LinkContainer $parentLink, Model $model) {
		foreach ($model->getChilds() as $name => $module) {
			$actionClass = get_class($module) . 'Action';
			$link = new SecureLink($module, $module->getTitle(), $module->getHide(), $actionClass);
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
	 * @param array $orig	The original trail (as array) to the searched link. (optional)
	 * @return Link
	 */
	public static function findLink(LinkContainer $linkcontainer, $trail, $orig = null) {
		if(substr($trail, 0, 1) == '/') {
			// remove leading slash
			$trail = substr($trail, 1);
		}

		$pathArray = explode('/', $trail);
		
		if($orig == null) {
			$orig = $pathArray;
		}

		$name = Value :: get($pathArray[0]);

		$link = $linkcontainer->getLink($name);

		try {
			unset ($pathArray[0]); // Delete the current link
			$link = self :: findLink($link, implode('/', $pathArray), $orig);
		} catch (LinkNotFoundException $e) {
			$thetrail = $orig;
			if(count($pathArray) > 0) {
				$thetrail = array_slice($orig, 0, -(count($pathArray)));
			}
			$name = end($thetrail);
			$link->setName($name);
			$link->setTrail('/' . implode('/', $thetrail));
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
		if(self::isRelativeTrail($trail)) {
			$newTrail = $base;

			if ($trail != '' and $newTrail != '/') {
				$newTrail .= '/';
			}

			$trail = $newTrail . $trail;
		}

		return $trail;
	}
	
	public static function isRelativeTrail($trail) {
		return substr($trail, 0, 1) != '/';
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

	private $hide;

	public function getHide() {
		return $this->hide;
	}
	
	private $trail = '';
	
	public function getTrail() {
		return $this->trail;
	}
	
	public function setTrail($trail) {
		$this->trail = $trail;
	}
	
	private $name = '';
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}

	function __construct($title, $hide) {
		$this->title = $title;
		$this->hide = $hide;
	}

	abstract public function execute(Processor $processor, Request $request, Response $response, Security $security);

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

	public function __construct(Model $model, $title, $hide, $action) {
		parent :: __construct($title, $hide);
		$this->model = $model;
		$this->action = $action;
	}

	public function execute(Processor $processor, Request $request, Response $response, Security $security) {
		$nick = $request->getUser() == null ? '' : $request->getUser()->getNick();
		if (!$this->isExecutable($request->getUser())) {
			throw new AccessDeniedException('User ' . $nick . ' can\'t access this page!');
		}
		elseif ($this->getSecurity() == 'high' && !$request->getUser()->getTrusted()) {
			throw new TrustedUserRequiredException('User ' . $nick . ' needs to be trusted to access this page!');
		}
		$trail = explode('/', substr($request->getTrail(), strlen($this->getTrail())));
		$trail[0] = $this->getName();
		
		$request->addParameters($trail);

		// load model data
		$this->getModel()->load();
		
		$action = new $this->action($processor, $request, $response, $this->getModel(), $security);
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
		parent :: __construct($title, $hide);
		$this->site = $site;
		$this->action = $action;
	}

	public function execute(Processor $processor, Request $request, Response $response, Security $security) {
		$action = new $this->action($processor, $request, $response, $this->site, $security);
		$action->execute();
	}

	public function isExecutable($user) {
		return true;
	}
}

class Request extends Bean {
	
	const METHOD_GET = 'GET';
	
	const METHOD_POST = 'POST';

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
	
	private $parts = array();
	
	public function getParts() {
		return $this->parts;
	}
	
	public function getPart($id) {
		return $this->parts[$id];
	}

	public function setTrail($trail) {
		$this->trail = $trail;
		
		if(substr($trail, 0, 1) == '/') {
			// remove leading slash
			$trail = substr($trail, 1);
		}
		$this->parts = explode('/', $trail);
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
		$this->setTrail($trail);
		$this->session = $session;
		$this->cookie = $cookie;
		$this->parameters = $parameters;
		$this->messages = $messages;
	}
}

class Response extends Bean {
	
	private $contentType = 'text/html';
	
	private $body = '';
	
	private $headers = array();
	
	public function getContentType() {
		return $this->contentType;
	}
	
	public function setContentType($contentType) {
		$this->contentType = $contentType;
	}
	
	public function getBody() {
		return $this->body;
	}
	
	public function setBody($body) {
		$this->body = $body;
	}
	
	public function getHeaders() {
		return $this->headers;
	}
	
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}
	
    public function setRedirect($url) {
        $this->setHeader('Location', $url);
    }
}

?>
