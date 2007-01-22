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
 * Import for Picasa Photo Albums into Anomey Gallery System
 * 
 * @autor Adrian Egloff<adrian.egloff@gmail.com>
 * @date 09.11.2006
 * 
 * 
 */
 
class PicasaImport extends Import {
	private $rss;
	
	public function __construct($sxml, $module) {
		parent::__construct($sxml, $module);
		
		$this->rss = (string) $sxml['rss'];
	}
	
	public function import(){
		
		$root = $this->module->getRoot();
		if(!isset($root))
			throw new ImportException("No rootitem.");
			
		if(!isset($this->item))
			throw new ImportException("Parentitem not found.");
		
		$this->item->deleteChildren();
		
		$this->importItem($this->item, $this->rss);

	}
	
	private function importItem($item, $rss){
		
		$file = file_get_contents($rss);
		if(!$file)
			throw new ImportException("There was an error while importing the sourcefile.");
		
		$rss = simplexml_load_string($file);
		
		if(isset($rss->category['term'])){
			$channel = $rss;
			$category = (string) $channel->category['term'];
		}
		else{
			$channel = $rss->channel[0];
			$category = (string) $channel->category;
		}
		
		switch($category){
			case 'user':
				$this->importUser($item, $channel);
				break;
			case 'album':
				$this->importAlbum($item, $channel);
				break;
			default:
				throw new ImportException("Wrong picasa rss file.");
				break;
		}
	}
	
	private function importUser($item, SimpleXMLElement $channel){
		foreach($channel->item as $childxml){
			$child = new PicasaAlbumItem($this->module, $item, $childxml);
			$item->addChild($child);
			$rss = (string)$childxml->guid.'&category=photo';
			$rss = str_replace('/data/entry/base/', '/data/feed/base/', $rss);
			$this->importItem($child, $rss);
		}
	}
	
	private function importAlbum($item, SimpleXMLElement $channel){		
		if(isset($channel->entry[0]))
			$children = $channel->entry;
		else
			$children = $channel->item;
		foreach($children as $child){
			$item->addChild(new PicasaPhotoItem($this->module, $item, $child));
		}
	}
	
	public function saveImport(SimpleXmlElement $sxml){
		$import = parent::saveImport($sxml);
		$import->addAttribute('rss', $this->rss);
		
		return $import;
	}
}

class PicasaAlbumItem extends GalleryItem {
	/*
	protected $module;
	protected $parent;
	
	protected $id;
	protected $title;
	protected $date;
	protected $class;
	
	protected $sxml;
	
	protected $thumbid;
	*/
	
	public function __construct($module, $parent, SimpleXMLElement $channel){
		$this->module = $module;
		$this->parent = $parent;
		
		$this->id = $module->getSettings()->getNextId();
		$this->title = (string) $channel->title;
		$this->date = (string) $channel->pubDate;
		$this->class = 'Gallery';

	}
}

class PicasaPhotoItem extends ImageItem {
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
	
	public function __construct($module, $parent, SimpleXMLElement $item){
		if(isset($item->description))
			$html = (string) $item->description;
		else
			$html = (string) $item->summary;
		$from = strpos($html, '" src="') + 7; // cut " src=" away
		$to = strpos($html, '" alt="') - 11; // cut ...=288 away
		
		$this->source = substr($html, $from, $to - $from);
		

		$this->module = $module;
		$this->parent = $parent;
		
		$this->id = $module->getSettings()->getNextId(); 
		$this->class = 'Image';
		
		// prepare cache
		$this->getThumb();
		$this->getItem();
	}
}
?>
