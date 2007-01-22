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

class ObjectNotFoundException extends Exception {
	
}

abstract class Object extends Bean {
	private $id = '';
	
	public function getId() {
		return $this->id;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function __construct() {
		
	}
}

class Store {	
	private $objects = array();
	
	public function getObjects() {
		return $this->objects;
	}
	
	private $nextId = 43;
	
	protected function getNextId() {
		return $this->nextId;
	}
	
	public function createObject(Object $object) {
		$object->setId('o'.$this->nextId++);
		$this->objects[$object->getId()] = $object;
	}
	
	public function getObject($id) {
		if(isset($this->objects[$id])) {
			return $this->objects[$id];
		} else {
			throw new ObjectNotFoundException('Object with id "'.$id.'" doesn\'t exist.');
		}
	}
	
	private $extensions = array();
	
	public function getExtensions() {
		return $this->extensions;
	}
	
	private $storeFile;
	
	private $profile = '';
	
	public function getProfile() {
		return $this->profile;
	}
	
	public function __construct($profile, $storeFile) {
		$this->profile = $profile;
		$this->storeFile = $storeFile;
	
		$this->loadExtensions();
		$this->loadModules();
			
		try {
			$storeXML = XML :: load($this->storeFile);
			$this->nextId = (int) $storeXML['nextid'];// load objects and permissions
			
			// load objects
			foreach ($storeXML->object as $objectElement) {
				$class = (string) $objectElement['class'];
				if(class_exists($class)) {
					$object = new $class();
					$object->setId((string) $objectElement['id']);
					$this->objects[$object->getId()] = $object;
				}
			}
		} catch (FileNotFoundException $e) {
			$site = new Site();
			$site->setId(Site::ID);
			$this->objects[Site::ID] = $site;
		}
	}

	private function loadModules() {
		$paths = array (
			'core/anomey/modules',
			$this->getProfile() . '/modules'
		);

		foreach ($paths as $path) {
			foreach (scandir($path) as $folder) {
				$apath = $path . '/' . $folder;

				if (is_dir($apath)) {
					if (file_exists($apath . '/'.$folder.'.module.php')) {
						include_once $apath . '/'.$folder.'.module.php';
					}
				}
			}
		}
	}

	private function loadExtensions() {
		$paths = array (
			'core/anomey/extensions',
			$this->getProfile() . '/extensions'
		);

		// load extensions
		foreach ($paths as $path) {
			foreach (scandir($path) as $folder) {
				$apath = $path . '/' . $folder;

				if (is_dir($apath)) {
					if (file_exists($apath . '/'.$folder.'.extension.php')) {
						include_once $apath . '/'.$folder.'.extension.php';
						$this->extensions[] = new $folder ();
					}
				}
			}
		}
	}

	public function save() {
		$xml = XML :: create('objects');
		$xml->addAttribute('nextid', $this->nextId);

		foreach ($this->getObjects() as $object) {
			$objectElement = $xml->addChild('object');
			$objectElement->addAttribute('id', $object->getId());
			$objectElement->addAttribute('class', get_class($object));
		}

		$xml->save($this->storeFile);
	}
}

?>
