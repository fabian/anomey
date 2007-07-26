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
 
class GalleryItem extends Composite {
	protected $thumbid;
	
	public function __construct($module, $parent, SimpleXMLElement $sxml){
		parent::__construct($module, $parent, $sxml);

		if($sxml['thumbid'])
			$this->thumbid = (int) $sxml['thumbid'];
	}
	
	public function getThumb(){
		if($this->thumbid){
			$item = $this->module->getRoot()->searchItem(array('id' => $this->thumbid));
			if($item != null)
				return $item->getThumb();
		}
		
		else if(count($this->children) > 0){
			return $this->children[array_rand($this->children)]->getThumb();
		}
		else{
			return 'no thumbnail';
		}
	}
	
	public function saveItem(SimpleXMLElement $sxml){
		$gallery = parent::saveItem($sxml);

		if($this->thumbid)
			$gallery->addAttribute('thumbid', $this->thumbid);
	}
	
}

?>
