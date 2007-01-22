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
 
 
/**
 * Import for Flikr Photo Albums into Anomey Gallery System
 * 
 * @autor Adrian Egloff<adrian.egloff@gmail.com>
 * @date 09.11.2006
 * 
 * @todo import a full user (all photosets of a user)
 * 
 */
 
class FlickrImport extends Import {
	private $photoset;
	
	public function __construct($sxml, $module) {
		parent::__construct($sxml, $module);
		
		$this->photoset = (string) $sxml['photoset'];
	}
	
	
	public function import(){
		$root = $this->module->getRoot();
		if(!isset($root))
			throw new ImportException("No rootitem.");
			
		
		if(!isset($this->item))
			throw new ImportException("Parentitem not found.");
		
		$this->item->deleteChildren();
		
		$params = array(
			'method'		=> 'flickr.photosets.getPhotos',
			'photoset_id'	=> $this->photoset,
		);
		
		$photos = $this->flickrRequest($params);
	
		foreach($photos['photoset']['photo'] as $photo){
			$this->item->addChild(new FlickrPhotoItem($this->module, $this->item, $photo));
		}
		
		
	}
	
	private function flickrRequest($params){
		$encoded_params = array();
		
		$encoded_params[] = 'api_key=02fdca83302b85e5c7595aab6a265b1f';
		$encoded_params[] = 'format=php_serial';
		
		foreach ($params as $k => $v){
			$encoded_params[] = urlencode($k).'='.urlencode($v);
		}
		
		$url = "http://api.flickr.com/services/rest/?".implode('&', $encoded_params);
		
		$rsp = file_get_contents($url);
		
		return unserialize($rsp);
	}
	
	
	public function saveImport(SimpleXmlElement $sxml){
		$import = parent::saveImport($sxml);
		$import->addAttribute('photoset', $this->photoset);
		
		return $import;
	}
}

class FlickrPhotoItem extends ImageItem {
	/*
	protected $module;
	protected $parent;
	
	protected $id;
	protected $title;
	protected $date;
	protected $class;
	
	protected $sxml;
	
	protected $source;
	*/
	
	public function __construct($module, $parent, $photo){
		$this->module = $module;
		$this->parent = $parent;
	
		$this->id = $module->getSettings()->getNextId();
		$this->class = 'Image';
		
		$this->source = 'http://static.flickr.com/'.$photo['server'].'/'.$photo['id'].'_'.$photo['secret'].'.jpg';
		
		// prepare cache
		$this->getThumb();
		$this->getItem();
	}
	
}
?>
