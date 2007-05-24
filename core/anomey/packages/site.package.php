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

require_once 'security.package.php';
require_once 'error.package.php';

class PageNotFoundException extends Exception {

}

abstract class Model extends SecurityObject {

	public function getMediaPath() {
		return $this->getSecurity()->getProfile().'/media/'.$this->getId();
	}
	
	public function getAllPermissions() {
		$permissions = array();
		foreach($this->getAvailablePermissions() as $name => $description) {
			$permissions[] = $this->getPermission($name);
		}
		
		return $permissions;
	}
	
	private $childs = array ();

	public function getChilds() {
		return $this->childs;
	}

	public function getChild($key) {
		if (isset ($this->childs[$key])) {
			return $this->childs[$key];
		} else {
			throw new Exception('Child with key "' . $key . '" not found.');
		}
	}

	public function addChild($key, $child) {
		if (!isset ($this->childs[$key])) {
			$this->childs[$key] = $child;
			return true;
		} else {
			return false;
		}
	}

	public function removeChild($key) {
		if (isset ($this->childs[$key])) {
			unset ($this->childs[$key]);
			return true;
		} else {
			return false;
		}
	}

	private $order = array ();

	public function moveChildUp($key) {
		// find out position of $key
		$i = -1;
		foreach ($this->childs as $k => $v) {
			if ($key === $k) {
				break;
			}
			$i++;
		}

		if ($i < 0)
			$i = 0;
		$new = array ();
		$x = 0;
		foreach ($this->childs as $k => $v) {
			if ($x++ == $i)
				$new[$key] = $this->childs[$key];
			if ($k !== $key)
				$new[$k] = $v;
		}
		$this->childs = $new;
	}

	public function moveChildDown($key) {
		// find out position of $key
		$i = 2;
		foreach ($this->childs as $k => $v) {
			if ($key === $k) {
				break;
			}
			$i++;
		}

		if ($i >= count($this->childs)) {
			$val = $this->childs[$key];
			unset ($this->childs[$key]);
			$this->childs[$key] = $val;
		} else {
			if ($i < 0)
				$i = 0;
			$new = array ();
			$x = 0;
			foreach ($this->childs as $k => $v) {
				if ($x++ == $i)
					$new[$key] = $this->childs[$key];
				if ($k !== $key)
					$new[$k] = $v;
			}
			$this->childs = $new;
		}
	}

	abstract public function getPath();

	private $title;

	public function getTitle() {
		return $this->title;
	}

	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @var Design
	 */
	private $design;

	public function getDesign() {
		return $this->design;
	}

	public function setDesign($design) {
		$this->design = $design;
	}

	private $parent = null;

	public function getParent() {
		return $this->parent;
	}

	public function setParent(Model $parent) {
		if ($parent->addChild($this->getName(), $this)) {
			if ($this->getParent() != null) {
				$this->getParent()->removeChild($this->getName());
			}
			$this->parent = $parent;
		}
	}

	public function __construct() {
		$this->init();
	}

	/**
	 * to be overwritten - gets called on initialize.
	 */
	public function init() {

	}

	/**
	 * to be overwritten - gets called before is used.
	 */
	public function load() {

	}

	public function save() {

	}

	public function remove() {

	}
}

/**
 * Module class.
 */
abstract class Module extends Model {
	
	public function getAvailablePermissions() {
		return array(
			'view' => 'view the content of the page',
			'edit' => 'edit the content of the page'
		);
	}
	
	public function getAuthor() {
		return '';
	}

	private $site;

	public function getSite() {
		return $this->site;
	}
	
	public function setSite(Site $site) {
		$this->site = $site;
	}

	private $name = '';

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}

	public function getPath() {
		$path = '';

		$page = $this;
		do {
			$path = '/' . $page->getName() . $path;
			$page = $page->getParent();
		} while ($page instanceof Module);

		return $path;
	}

	private $hide = true;

	public function getHide() {
		return $this->hide;
	}

	public function setHide($hide) {
		$this->hide = $hide;
	}
	
	private $modified = '';
	
	public function getModified() {
		return $this->modified;
	}
	
	public function setModified($modified) {
		$this->modified = $modified;
	}
	
	public function setModifiedNow() {
		$this->setModified(date('Y-m-d'));
	}

	public function getType() {
		return get_class($this);
	}

	private $xml;

	public function getXml() {
		if (!isset ($this->xml)) {
			$this->xml = XML :: load($this->getSecurity()->getProfile() . '/xml/modules/' . $this->getId() . '.xml');
		}

		return $this->xml;
	}

	public function save() {
		$xml = XML :: create('empty');
		$this->store($xml);
	}

	public function remove() {
		$file = $this->getSecurity()->getProfile() . '/xml/modules/' . $this->getId() . '.xml';
		if(file_exists($file)) {
			unlink($file);
		}
		FileSytem::rmdir($this->getSecurity()->getProfile() . '/media/' . $this->getId());
		FileSytem::rmdir($this->getSecurity()->getProfile() . '/tmp/cache/' . $this->getId());
	}

	public function store(XML $xml) {
		if(!file_exists($this->getSecurity()->getProfile() . '/xml/modules')) {
			mkdir($this->getSecurity()->getProfile() . '/xml/modules');
		}
		
		$file = $this->getSecurity()->getProfile() . '/xml/modules/' . $this->getId() . '.xml';
		$xml->save($file);

		$this->xml = $xml;
	}
}

class NameAlreadyInUseException extends Exception {

}

class ModuleNotFoundException extends Exception {

}

class ActionLink {
	private $links = array ();

	public function getLinks() {
		return $this->links;
	}

	public function add(ActionLink $link) {
		$this->links[] = $link;
	}
	
	private $class;
	
	public function getClass() {
		return $this->class;
	}
	
	private $name;
	
	public function getName() {
		return $this->name;
	}
	
	private $hide;
	
	public function getHide() {
		return $this->hide;
	}
	
	private $title;
	
	public function getTitle() {
		return $this->title;
	}
	
	public function __construct($class, $name, $hide, $title) {
		$this->class = $class;
		$this->name = $name;
		$this->hide = $hide;
		$this->title = $title;
	}
	
	private $security = 'normal';
	
	public function getSecurity() {
		return $this->security;
	}
	
	public function setSecurity($security) {
		$this->security = $security;
	}

	private $requiredPermissions = array ();

	public function getRequiredPermissions() {
		return $this->requiredPermissions;
	}

	public function addRequiredPermission($permission) {
		$this->requiredPermissions[] = $permission;
	}
}

class ModuleConfiguration {
	
	private $actionLink;
	
	public function getActionLink() {
		return $this->actionLink;
	}
	
	private $xml;
	
	public function getXML() {
		return $this->xml;
	}
	
	public function __construct(XML $xml) {
		$this->xml = $xml;
		$this->actionLink = self::createActionLink($xml->mainAction);
	}
	
	private static function createActionLink(SimpleXMLElement $element) {
		$hide = (string) $element['hide'] == 'false' ? false : true;
		$link = new ActionLink((string) $element['class'], (string) $element['name'], $hide, (string) $element['title']);
		if($element->requiredPermissions) {
			if (count($element->requiredPermissions->permission) > 0) {
				foreach ($element->requiredPermissions->permission as $permission) {
					$link->setSecurity(Processor::readSecurity($element->requiredPermissions));
					if ((string) $permission != '') {
						$link->addRequiredPermission((string) $permission);
					}
				}
			}
		}
		self::readActionLinks($element, $link);
		
		return $link;
	}
	
	/**
	 * Reads xml element into ActionLink.
	 */
	public static function readActionLinks(SimpleXMLElement $element, ActionLink $parent) {
		foreach($element->action as $action) {
			$link = self::createActionLink($action);
			$parent->add($link);
		}
	}

	public static function parseRequiredPermissions(SimpleXMLElement $element, ActionLink $link) {

	}
}

interface TextParser {
	public function parse(Model $model, $text);
}

/**
 * Site class
 */
class Site extends Model {
	
	const ID = 'o42';

	/**
	 * Sitemap xml.
	 */
	private $sitemap;

	private $designs = array ();

	public function getDesigns($name = null) {
		if($name == null) {
			return $this->designs;
		} else {
			if(isset($this->designs[$name])) {
				return $this->designs[$name];
			} else {
				throw new DesignNotFoundException('Design with name "' . $name . '" not found!');
			}
		}
	}

	public function getPath() {
		return '/';
	}
	
	private $store;

	public function invoke($sitemap, Store $store) {		
		$this->sitemap = $sitemap;
			
		$this->store = $store;

		$path = $this->store->getProfile().'/designs';

		foreach (scandir($path) as $name) {
			if (file_exists($path . '/' . $name . '/design.xml')) {
				$designXml = XML :: load($path . '/' . $name . '/design.xml');
				$title = (string) $designXml->title;
				$author = (string) $designXml->author->name;
				$license = (string) $designXml->license;
				$this->designs[$name] = new Design($this->store->getProfile(), $name, $title, $author, $license);
			}
		}

		$this->loadModules();
		
		// set default design
		$this->setDesign(new Design($this->store->getProfile(), '', 'Default', 'anomey team', 'GPL'));
		
		try {
			$xml = Xml :: load($this->sitemap);
			
			$title = (string) $xml['title'];
			$design = (string) $xml['design'];
			
			$this->setTitle($title);
			
			if(isset($this->designs[$design])) {
				$this->setDesign($this->designs[$design]);
			}			

			$this->createIndex($xml, $this);
		} catch(FileNotFoundException $e) {
			$this->setTitle('anomey');
			
			$this->index[$this->getId()] = $this;
			
			$home = new Text();
			$home->setSecurity($this->store);
			$home->setSite($this);
			$home->setName('home');
			$home->setTitle('Home');
			$home->setHide(false);
			$this->store->createObject($home);
			$this->index[$home->getId()] = $home;
			$home->setParent($this);
			$home->setContent('Welcome to anomey. Log in with username "root" and password "root" and ' .
					'change your username and password as soon as possible.');
			if($home instanceof SecurityObject and $this->store instanceof Security) {
				$viewPermission = new Permission('view');
				$viewPermission->setEveryone(true);
				$home->addPermission($viewPermission);
				$editPermission = new Permission('edit');
				$editPermission->addUser($this->store->getUser('u0'));
				$home->addPermission($editPermission);
			}
			$this->store->save();
			$this->save();
			$home->save();
		}
	}
	
	private $textParsers = array();
	
	public function addTextParser(TextParser $parser) {
		$this->textParsers[] = $parser;
	}
	
	public function parseText(Model $model, $text) {
		foreach($this->textParsers as $parser) {
			$text = $parser->parse($model, $text);
		}
		return $text;
	}

	public function getAvailablePermissions() {
		return array (
			'admin' => 'administrate the site'
		);
	}

	private $modules = array ();

	private function loadModules() {
		$paths = array (
			'core/anomey/modules',
			$this->store->getProfile().'/modules'
		);

		foreach ($paths as $path) {
			foreach (scandir($path) as $folder) {
				$apath = $path . '/' . $folder;

				if (is_dir($apath)) {
					if (file_exists($apath . '/'.$folder.'.module.php')) {
						$this->modules[] = $folder;
					}
				}
			}
		}

		sort($this->modules);
	}

	public function getAvaibleModules() {
		return $this->modules;
	}
	
	public function canSave() {
		return is_writable($this->sitemap);
	}

	public function save() {
		$this->store->save();
		$xml = XML :: create('site');
		$xml->addAttribute('id', $this->getId());
		$xml->addAttribute('title', $this->getTitle());
		if($this->getDesign()->getName() != '') {
			$xml->addAttribute('design', $this->getDesign()->getName());
		}

		$this->createXML($xml, $this->getChilds());

		return $xml->save($this->sitemap);
	}

	private function createXML(SimpleXMLElement $parent, $modules) {
		foreach ($modules as $module) {
			$xml = $parent->addChild('page');
			$xml->addAttribute('id', $module->getId());
			$xml->addAttribute('title', $module->getTitle());
			$xml->addAttribute('name', $module->getName());
			$xml->addAttribute('modified', $module->getModified());

			if (!$module->getHide()) {
				$xml->addAttribute('hide', 'false');
			}

			$this->createXML($xml, $module->getChilds());
		}
	}

	public function createPage($title, $name, Model $parent, $type, $hide, $user) {
		$page = new $type ();
		$page->setSecurity($this->store);
		$page->setSite($this);
		$page->setName($name);
		$page->setModifiedNow();
		$page->setTitle($title);
		$page->setHide($hide);
		$page->setDesign($this->getDesign());
		$this->store->createObject($page);
		$this->index[$page->getId()] = $page;
		$page->setParent($parent);
		$page->save();
		if($user != null and $page instanceof SecurityObject and $this->store instanceof Security) {
			foreach($page->getAvailablePermissions() as $name => $description) {
				$permission = new Permission($name);
				$permission->addUser($user);
				$page->addPermission($permission);
			}
			$this->store->save();
		}
		
		return $page;
	}

	private $index = array ();

	public function getPage($id) {
		if (isset ($this->index[$id])) {
			return $this->index[$id];
		} else {
			throw new PageNotFoundException('Page with id "' . $id . '" not found!');
		}
	}
	
	public function deletePage(Module $page) {
		// delete childs
		foreach($page->getChilds() as $child) {
			$this->deletePage($child);
		}
		
		$page->getParent()->removeChild($page->getName());
		$page->remove();
		
		$this->store->removeObject($page->getId());
	}

	public function createIndex(SimpleXMLElement $xml, Model $parent) {
		// add parent model to index
		$this->index[$parent->getId()] = $parent;

		$pages = array ();
		$elements = $xml->page;

		foreach ($elements as $element) {
			$id = (string) $element['id'];
			$name = (string) $element['name'];
			$modified = (string) $element['modified'];
			$title = (string) $element['title'];
			$hide = (string) $element['hide'];

			$hide = $hide == 'false' ? false : true;

			try {
				$page = $this->store->getObject($id);
				$page->setSite($this);
				$page->setName($name);
				$page->setModified($modified);
				$page->setTitle($title);
				$page->setHide($hide);
				$page->setDesign($this->getDesign());
				$page->setParent($parent);
	
				$this->createIndex($element, $page);
			} catch(ObjectNotFoundException $e) {
				
			}
		}
	}
}
?>
