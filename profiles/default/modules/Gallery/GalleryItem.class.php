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
