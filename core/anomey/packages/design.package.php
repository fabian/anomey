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

class Design extends Smarty implements ArrayAccess {

	private $profile;
	
	private $name;
	
	private $title;
	
	private $author;
	
	private $license;

	public function __construct($profile, $name, $title, $author, $license) {
		parent::__construct();
		
		if (!file_exists($profile . '/tmp/templates')) {
			mkdir($profile . '/tmp/templates');
		}
		
		if (!file_exists($profile . '/tmp/cache')) {
			mkdir($profile . '/tmp/cache');
		}

		$this->compile_dir = $profile . '/tmp/templates';
		$this->cache_dir = $profile . '/tmp/cache';
		$this->plugins_dir = array (
			'core/smarty/plugins',
			'core/anomey/smarty'
		);

		$this->profile = $profile;
		$this->name = $name;
		$this->title = $title;
		$this->author = $author;
		$this->license = $license;

		$path = array ();

		$path[] = $this->getFolder();
		$path[] = 'core/anomey/templates';

		foreach (array (
				'core/anomey/modules',
				'core/anomey/extensions',
				$profile . '/modules',
				$profile . '/extensions'
			) as $additionalPath) {
			foreach (scandir($additionalPath) as $element) {
				$path[] = $additionalPath . '/' . $element . '/templates';
			}
		}

		$this->template_dir = $path;
		
		$this->register_modifier('anomey', array(&$this, 'parseText')); 
	}
	
	public function getFolder() {
		return $this->profile . '/designs/' . $this->name . '/templates';
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	public function getAuthor() {
		return $this->author;
	}
	
	public function setAuthor($author) {
		$this->author = $author;
	}
	
	public function getLicense() {
		return $this->license;
	}
	
	public function setLicense($license) {
		$this->license = $license;
	}
	
	public function parseText($string) {
		$model = $this->get_template_vars('model');
		if($model->getSite() != null) {
			$string = $model->getSite()->parseText($model, $string);
		}
		return $string;
	}

	public function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
		$id = $this->name;
		return parent :: fetch($resource_name, $id, $id, $display);
	}

	public function _parse_resource_name(& $params) {
		$return = parent :: _parse_resource_name($params);
		$this->assign('resources', str_replace('\\', '/', dirname($params['resource_name'])) . '/');
		return $return;
	}
	
	// implementing Bean functionality
	
	public function offsetGet($key) {
		$key = preg_replace('/\W/', '', $key);
		$method = 'get' . ucfirst($key);

		if ($this->offsetExists($method)) {
			return $this-> $method ();
		}
	}

	public function offsetExists($key) {
		return method_exists($this, $key);
	}

	public function offsetSet($key, $value) {
		$key = preg_replace('/\W/', '', $key);
		$method = 'set' . ucfirst($key);

		if ($this->offsetExists($method)) {
			return $this-> $method ($value);
		}
	}

	public function offsetUnset($key) {
	}
}

class DesignNotFoundException extends Exception {

}

?>
