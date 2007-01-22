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

abstract class Bean implements ArrayAccess {

	function offsetGet($key) {
		$key = preg_replace('/\W/', '', $key);
		$method = 'get' . ucfirst($key);

		if ($this->offsetExists($method)) {
			return $this-> $method ();
		}
	}

	function offsetExists($key) {
		return method_exists($this, $key);
	}

	function offsetSet($key, $value) {
		$key = preg_replace('/\W/', '', $key);
		$method = 'set' . ucfirst($key);

		if ($this->offsetExists($method)) {
			return $this-> $method ($value);
		}
	}

	function offsetUnset($key) {
	}
}

class File extends Bean {
	private $name;
	
	private $modified;
	
	public function __construct($name, $modified) {
		$this->name = $name;
		$this->modified = $modified;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getModified() {
		return $this->modified;
	}
}

class FileSytem {
	
	const CHARS = '[-_a-zA-Z0-9\.]';

	public static function get($path) {
		$dir = new DirectoryIterator($path);
		$files = array();
		foreach ($dir as $file) {
			if ($file->isFile()) {
				$files[] = new File($file->getFilename(), $file->getCTime());
			}
		}
		return $files;
	}
	
	public static function rmdir($dirname) {
		if(is_dir($dirname)) {
			$directory = new RecursiveDirectoryIterator($dirname);
			
			foreach($directory as $subDirectory) {
				if(!@rmdir($subDirectory)) {
					self :: rmdir($subDirectory);
				}
			}
			
			foreach(new RecursiveIteratorIterator($directory) as $file) {
				unlink($file);
			}
			
			return rmdir($dirname);
		} else {
			return false;
		}
	}
	
}

class URI extends Bean {
	
	const CHARS = '[-_\/a-zA-Z0-9]';
	
	private $scheme = '';
	
	public function getScheme() {
		return $this->scheme;
	}
	
	public function setScheme($scheme) {
		$this->scheme = $scheme;
	}
	
	private $hierarchy = '';
	
	public function getHierarchy() {
		return $this->hierarchy;
	}
	
	public function setHierarchy($hierarchy) {
		$this->hierarchy = $hierarchy;
	}
	
	private $query = '';
	
	public function getQuery() {
		return $this->query;
	}
	
	public function setQuery($query) {
		$this->query = $query;
	}
	
	private $fragment = '';
	
	public function getFragment() {
		return $this->fragment;
	}
	
	public function setFragment($fragement) {
		$this->fragment = $fragement;
	}
	
	public function __construct($scheme) {
		$this->scheme = $scheme;
	}

	public function toString() {
		$string = $this->getScheme() . ':' . $this->getHierarchy();
		if($this->getQuery() != '') {
			$string .= '?' . $this->getQuery();
		}
		if($this->getFragment() != '') {
			$string .= '#' . $this->getFragment();
		}
		return $string;
	}
	
	public function getFull() {
		return $this->toString();
	}
}

class URL extends URI {

	private $host = '';
	
	public function getHost() {
		return $this->host;
	}
	
	public function setHost($host) {
		$this->host = $host;
	}

	private $path = '';

	public function getPath() {
		return $this->path;
	}
	
	public function setPath($path) {
		$this->path = $path;
	}

	private $base = '';

	public function getBase() {
		return $this->base;
	}
	
	public function setBase($base) {
		$this->base = $base;
	}

	function __construct($scheme, $host, $path, $base) {
		parent::__construct($scheme);
		$this->setHost($host);
		$this->setPath($path);
		$this->setBase($base);
	}
	
	public function getHierarchy() {
		return '//' . $this->getHost() . $this->getPath();
	}
	
	public function getServer() {
		return $this->getScheme() . '://' . $this->getHost();
	}
}

class Collection extends ArrayObject {
	
	public function set($key, $value) {
		$this[$key] = $value;
	}
	
	public function get($key) {
		return $this[$key];
	}
	
	public function remove($key) {
		unset($this[$key]);
	}
	
	public function first() {
		return reset($this->getArrayCopy());
	}
	
	public function exists($key) {
		return isset($this[$key]);
	}
	
	public function contains($value) {
		return in_array($value, (array) $this);
	}
	
	public function krsort() {
		$array = $this->getArrayCopy();
		krsort($array);
		$this->exchangeArray($array);
	}
	
	public function merge(Collection $array) {
		$this->exchangeArray(array_merge((array) $this, (array) $array));
	}
	
	public function unshift($value) {
		array_unshift($this, $value);
	}
	
	public function map($function) {
		array_map($function, $this);
	}
	
	public function getKeys() {
		return array_keys($this);
	}
	
	public function toReadableString() {
		return self::readableString((array) $this);
	}
	
	public static function readableString($array) {
		$string = '';
		reset($array);
		$size = count($array);

		if ($size > 0) {
			$string .= current($array);

			if ($size > 1) {
				next($array);

				$i = 1;
				while ($i < $size -1) {
					$string .= ', ' . current($array);
					next($array);
					$i++;
				}

				$string .= ' and ' . current($array);
			}
		}

		return $string;
	}
}

abstract class Value {
	public static function get(& $variable, $default = '') {
		if (isset ($variable)) {
			return $variable;
		} else {
			return $default;
		}
	}
}

abstract class HTML {
	/**
	* This function adapts htmlentities()
	* to the $variable. If the variable is an array,
	* the function htmlentities will be adapt to the
	* containing $variables recursively. 
	*
	* @author Fabian Vogler <fabian@ap04a.ch>
	* @param mixed $variable
	* @return mixed
	*/
	public static function entities($value) {
		if (is_array($value)) {
			$value = array_map(array (
				'HTML',
				'entities'
			), $value);
		}
		elseif (!is_object($value)) {
			$value = htmlentities($value);
		}
		return $value;
	}

	/**
	* This function adapts htmlspecialchars()
	* to the $variable. If the variable is an array,
	* the function htmlspecialchars will be adapt to the
	* containing $variables recursively. 
	*
	* @author Fabian Vogler <fabian@ap04a.ch>
	* @param mixed $variable
	* @return mixed
	*/
	public static function specialchars($value) {
		if (is_array($value)) {
			$value = array_map(array (
				'HTML',
				'specialchars'
			), $value);
		}
		elseif (!is_object($value)) {
			$value = htmlspecialchars($value);
		}
		return $value;
	}

	/**
	* This function adapts htmlspecialchars_decode()
	* to the $variable. If the variable is an array,
	* the function htmlspecialchars_decode will be adapt to the
	* containing $variables recursively. 
	*
	* @author Fabian Vogler <fabian@ap04a.ch>
	* @param mixed $variable
	* @return mixed
	*/
	public static function decodeSpecialchars($value) {
		if (is_array($value)) {
			$value = array_map(array (
				'HTML',
				'decodeSpecialchars'
			), $value);
		}
		elseif (!is_object($value)) {
			$value = htmlspecialchars_decode($value);
		}
		return $value;
	}
}

abstract class String {
	public static function truncate($string, $maxlength) {
		$stringArray = explode('.', $string);

		$newString = '';

		if (count($stringArray) > 1) {
			$letters = strlen($stringArray[0]) + 1;
			for ($i = 0; $letters <= $maxlength && $i < count($stringArray); $i++) {
				$newString .= $stringArray[$i] . '.';
				if (isset ($stringArray[$i +1])) {
					$letters += strlen($stringArray[$i +1]) + 1;
				}
			}
		}
		return $newString;
	}

	public static function stripslashes($value) {
		if (is_array($value)) {
			$value = array_map(array (
				'String',
				'stripslashes'
			), $value);
		}
		elseif (!is_object($value)) {
			$value = stripslashes($value);
		}
		return $value;
	}
}

class Message extends Bean {

	private $value;
	private $type;
	private $displayed = false;

	function __construct($value, $type = 'info') {
		$this->value = $value;
		$this->type = $type;
	}

	public function getValue() {
		$this->displayed = true;
		return $this->value;
	}

	public function getType() {
		return $this->type;
	}

	public function isDisplayed() {
		return $this->displayed;
	}
}

class ErrorMessage extends Message {
	const TYPE = 'error';

	public function __construct($value) {
		parent :: __construct($value, self :: TYPE);
	}
}

class WarningMessage extends Message {
	const TYPE = 'warning';

	public function __construct($value) {
		parent :: __construct($value, self :: TYPE);
	}
}

class ApplicationError extends Exception {
}

class FileNotFoundException extends Exception {
}
?>
