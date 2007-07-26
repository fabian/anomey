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

class ImageItem extends Leaf {
	protected $source;
	protected $type;
	
	protected $thumb;
	protected $image;
	
	public function __construct($module, $parent, SimpleXMLElement $sxml){
		parent::__construct($module, $parent, $sxml);
		
		$this->source = (string) $this->sxml['source'];
		$this->type = (string) $this->sxml['type'];
		
		if($this->type == ""){
			$image_info = getimagesize($this->source);
			if(!$image_info){
				throw new ImportException("Could not load image.");
			}
			switch ($image_info['mime']) {
				case 'image/gif':
					$this->type = 'gif';
					break;
				case 'image/jpeg':
					$this->type = 'jpeg';
					break;
				case 'image/png':
					$this->type = 'png';
					break;
				default:
					throw new ImportException("Unknown image format.");
			}
		}
	}
	
	public function setType($type){
		$this->type = $type;
	}
	
	public function getType(){
		return $this->type;
	}
	
	public function getSource(){
		return $this->source;
	}
	
	public function getCache($settings){
		$storage = $this->module->getStorage();
		return $storage['cache'].$this->getHash($settings);
	}
	
	public function getItem(){
		return $this->getImage($this->module->getSettings()->getImageSettings());
	}
	
	public function getThumb(){
		return $this->getImage($this->module->getSettings()->getThumbSettings());
	}
	
	private function getHash($settings){
		return $this->class.'__'.$this->id.'__'.$settings['width'].'x'.$settings['height'].'__CROP'.($settings['crop']?'true':'false').'.'.$this->type;
	}
	
	private function getImage($settings){
		$storage = $this->module->getStorage();
		$filename = $this->getHash($settings);
		$cache = $storage['cache'].$filename;
		$web = $storage['web'].$filename;
		
		if(!file_exists($cache)){
			try {
				$this->module->prepareImage($this, $settings);
			}
			catch (ImportException $e){
				$this->source = $this->module->getDefaultImage();
				$this->module->prepareImage($this, $settings);
			} 
		}
		
		return $web;
	}
	
	public function saveItem(SimpleXMLElement $sxml){
		$image = parent::saveItem($sxml);
		
		$image->addAttribute('source', $this->source);
		$image->addAttribute('type', $this->type);
		
		return $image;
	}
}
?>
