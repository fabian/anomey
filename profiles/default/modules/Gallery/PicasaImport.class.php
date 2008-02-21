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

	public function doit(){
		
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

		$term = $rss->category['term'];

		$category = substr($term, strrpos($term, '#')+1);

		switch($category){
			case 'user':
				$this->importUser($item, $rss);
				break;
			case 'album':
				$this->importAlbum($item, $rss);
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
		print("image");
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
		/*
		 if(isset($item->description))
			$html = (string) $item->description;
			else
			$html = (string) $item->summary;
			$from = strpos($html, '" src="') + 7; // cut " src=" away
			*/
		/*
		 $url = $item->enclosure['url'];
		 $to = strpos($url, '" alt="') - 11; // cut ...=288 away

		 $this->source = substr($url, 0, $to);
		 */

		$this->source = $item->content['src'];

		switch ($item->content['type']) {
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
