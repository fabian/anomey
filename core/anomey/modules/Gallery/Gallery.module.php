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
 * Gallery System in anomey 2.1
 * 
 * @autor Adrian Egloff<adrian.egloff@gmail.com>
 * @date 08.11.2006
 * 
 * @todo Fallback "NoThumbnail" Image ??
 * @todo Gallery2 plugin ??
 * 
 * Last Changes:
 * - Thumbnail fallback with random Thumbnails
 * - Implemented the Composite Pattern for the Tree Structure
 * - Image Cach implemented
 * - Import for Picasa and Flickr Galleries
 * - Admin Import
 * 
 */

include_once('Composite.pattern.php');
include_once('GalleryItem.class.php');
include_once('ImageItem.class.php');

class Gallery extends Module {
	private $settings;
	private $root;
	private $item;
	private $storage = array();
	private $imports = array();
	private $sxml;
	
	public function getAvailablePermissions() {
		return array (
			'read' => 'take a look at the gallery',
			'edit' => 'change items in the gallery'
		);
	}
	
	public function load() {
		try {
			$this->sxml = $this->getXml();
		}
		catch (FileNotFoundException $e) {
			$this->save();
			try {
				$this->sxml = $this->getXml();
			}
			catch (FileNotFoundException $e) {}
		}
		
		$this->loadSettings();
		$this->loadItems();
		$this->loadImports();
		
		/*	// sample import function for first and second import
		try{
			$this->imports[0]->import($this);
			$this->imports[1]->import($this);
		}
		catch(ImportException $e){
			echo $e->getMessage();
		}
		
		$this->save();*/
	}
	
	private function loadSettings(){
		if(isset($this->sxml->settings)){
			$this->settings = new Settings($this->sxml->settings);
		}
		else{
			// load default settings
			$default = simplexml_load_string(
					'<settings cols="3" rows="4" nextid="100">' .
					'	<thumb width="100" height="100" crop="true" />' .
					'	<image width="800" height="600" crop="false" />' .
					'</settings>');
			$this->settings = new Settings($default);
		}
	}
	
	private function loadImports(){
		if(isset($this->sxml->imports)){
			foreach($this->sxml->imports[0]->import as $importxml){
				$this->addImport($importxml);
			}
		}
	}
	
	public function addImport($importxml){
		$class = (string) $importxml['class'].'Import';
		if(file_exists('./core/anomey/modules/Gallery/' . $class . '.class.php'))
			include_once($class . '.class.php');
		
		if(class_exists($class)){
			$import = new $class($importxml, $this);
			if($import != null){
				$this->imports[(int) $importxml['item']] = $import;
			}
		}
	}
	
	public function deleteImport($id){
		$imports = array();
		foreach ($this->imports as $key => $value){
			if($key != $id)
				$imports[$key] = $value;
		}
		$this->imports = $imports;
	}
	
	private function loadItems(){
		/**
		 * Note: 	There can only be one TopLevel Item (and that is $xml->item[0]), other TopLevel items will be ignored
		 */
		
		// load default Picasa/Flickr imports
		if(!isset($this->sxml->item[0])){
			if(!isset($this->sxml->imports))
				$imports = $this->sxml->addChild('imports');
			else
				$imports = $this->sxml->imports[0];
			
			$import = $imports->addChild('import');
			$import->addAttribute('class', 'Picasa');
			$import->addAttribute('item', '10');
			$import->addAttribute('rss', 'http://picasaweb.google.com/lh/rssAlbum?uname=adrian.egloff&aid=4984938126230224913');

			$import = $imports->addChild('import');
			$import->addAttribute('class', 'Flickr');
			$import->addAttribute('item', '11');
			$import->addAttribute('photoset', '72157594335647157');
			
			$root = $this->sxml->addChild('item');
			$root->addAttribute('class', 'Gallery');
			$root->addAttribute('title', 'Default Galleries');
			
			$gallery = $root->addChild('item');
			$gallery->addAttribute('class', 'Gallery');
			$gallery->addAttribute('id', '10');
			$gallery->addAttribute('title', 'Picasa Testgallery');
			
			$gallery = $root->addChild('item');
			$gallery->addAttribute('class', 'Gallery');
			$gallery->addAttribute('id', '11');
			$gallery->addAttribute('title', 'Flickr Testgallery');

		}
		$this->root = $this->loadItem(null, $this->sxml->item[0]);
	}
	
	public function setStorage($url_path){
		$this->storage['local'] = $this->getSecurity()->getProfile() . '/media/'.$this->getId().'/galleries/';
		$this->storage['cache'] = $this->getSecurity()->getProfile() . '/tmp/cache/'.$this->getId().'/';
		$this->storage['web'] = $url_path. $this->getSecurity()->getProfile() . '/tmp/cache/'.$this->getId().'/';
		
		if(!file_exists($this->storage['cache'])){
			// create cache-folder (recursive)
			mkdir($this->storage['cache'], 0755, true);
		}
		if(!file_exists($this->storage['local'])){
			// create media-folder (recursive)
			mkdir($this->storage['local'], 0755, true);
		}
	}
	
	public function loadItem($parentitem, SimpleXMLElement $sxml){
		$class = (string) $sxml['class'].'Item';
		if(file_exists($this->getSecurity()->getProfile() . '/modules/Gallery/'.$class.'.class.php'))
			include_once($class.'.class.php');
		
		if(class_exists($class)){
			$item = new $class($this, $parentitem, $sxml);
		
			foreach($sxml->item as $childxml){
				$item->addChild($this->loadItem($item, $childxml));
			}
		}
		return $item;
	}

	public function selectItem($parameter){
		if(isset($this->root))
			if(!$this->item = $this->root->searchItem($parameter))
				$this->item = $this->root;
	}
	
	public function getStorage() {
		return $this->storage;
	}
	
	public function getSettings() {
		return $this->settings;
	}
	
	public function getImports() {
		return $this->imports;
	}
	
	public function getImport($id) {
		if(isset($this->imports[$id]))
			return $this->imports[$id];
	}

	public function getItem() {
		return $this->item;
	}
	
	public function getRoot() {
		return $this->root;
	}
	
	public function getGalleryPath(){
		$path = array();
			if(isset($this->root)){
			$item = $this->item;
			
			while($item->getParent()){
				$item = $item->getParent();
				$path[] = $item;
			}
		}
		return array_reverse($path);
	}
	
	public function resizeImage($source, $settings){
		$image_info = getimagesize($source);
		if(!$image_info){
			return null;
		}

		switch ($image_info['mime']) {
			case 'image/gif':
				$image = imagecreatefromgif($source) ;
				break;
			case 'image/jpeg':
				$image = imagecreatefromjpeg($source) ;
				break;
			case 'image/png':
				$image = imagecreatefrompng($source) ;
				break;
		}
		
		$width_src = $image_info[0];
		$height_src = $image_info[1];
		
		$width_dst = $settings['width'];
		$height_dst = $settings['height'];
		
		if($width_src < $width_dst && $height_src < $height_dst){
			$resized = $image;
		}
		else if(!$settings['crop']){
			// resize only
			if($width_src > $width_dst || $height_src > $height_dst){
				if($width_dst / $height_dst < $width_src / $height_src){
					$percent = $width_dst / $width_src;
				}
				else {
					$percent = $height_dst / $height_src;
				}
			}
			else{
				$percent = 1;
			}
			
			$width_calc = $width_src * $percent;
			$height_calc = $height_src * $percent;
			
			$resized = imagecreatetruecolor($width_calc, $height_calc);
			
			imagecopyresampled($resized, $image, 0, 0, 0, 0, $width_calc, $height_calc, $width_src, $height_src);
		}
		else{
			// resize and crop
			$off_w = 0;
			$off_h = 0;
			
			if($width_dst / $height_dst > $width_src / $height_src){
				$percent = $width_dst / $width_src;
				$off_h = $height_src - ($height_dst / $percent);
			}
			else {
				$percent = $height_dst / $height_src;
				$off_w = $width_src - ($width_dst / $percent);
			}
			
			$width_calc = $width_src * $percent;
			$height_calc = $height_src * $percent;
			
			$resized = imagecreatetruecolor($width_dst, $height_dst);
			
			imagecopyresampled($resized, $image, -($width_calc/2) + ($width_dst/2), -($height_calc/2) + ($height_dst/2), 0, 0, $width_calc, $height_calc, $width_src, $height_src);
		
		}
		
		return $resized;
	}

	public function addGallery($title, $date, $parent){
		$this->selectItem(array('id' => $parent));
		$parent = $this->getItem();
		$id = $this->getSettings()->getNextId();

		$gallery = simplexml_load_string('<item class="Gallery" id="'.$id.'" title="'.$title.'" date="'.$date.'" />');
		
		$parent->addChild(new GalleryItem($this, $parent, $gallery));
		return $id;
	}
	
	public function deleteGallery(){
		if($this->root == $this->item)
			return false;
		else{
			if(isset($this->imports[$this->item->getId()]))
				$this->deleteImport($this->item->getId());
			$this->item->getParent()->deleteItem($this->item->getId());
			return true;
		}
			
	}

	public function addImage($source, $parent){
		$this->selectItem(array('id' => $parent));
		$parent = $this->getItem();
		$id = $this->getSettings()->getNextId();
		
		$image = simplexml_load_string('<item class="Image" id="'.$id.'" source="'.$source.'" />');
		
		$parent->addChild(new ImageItem($this, $parent, $image));
		return $id;
	}

	// save everything back to the xmlfile
	public function save(){
		$sxml = XML::create('module');
		
		if(isset($this->settings))
			$this->settings->saveSettings($sxml);
		
		if(isset($this->imports)){
			$imports = $sxml->addChild('imports');
			foreach($this->imports as $import){
				$import->saveImport($imports);
			}
		}

		if(isset($this->root))
			$this->root->saveItem($sxml);
		
		
		$this->store($sxml);
	}
}

class Settings extends Bean {
	private $cols;
	private $rows;
	private $nextid;
	private $thumb;
	private $image;
	
	public function __construct($sxml) {
		$this->cols = (int) $sxml['cols'];
		$this->rows = (int) $sxml['rows'];
		$this->nextid = (int) $sxml['nextid'];
		$this->thumb = array(	'width' => (int) $sxml->thumb[0]['width'], 
								'height' => (int) $sxml->thumb[0]['height'], 
								'crop' => (string) $sxml->thumb[0]['crop'] == 'false' ? false : true
							);
		$this->image = array(	'width' => (int) $sxml->image[0]['width'], 
								'height' => (int) $sxml->image[0]['height'], 
								'crop' => (string) $sxml->image[0]['crop'] == 'false' ? false : true
							);
	}
	
	public function getFolder() {
		return $this->folder;
	}
	
	public function getCols() {
		return $this->cols;
	}
	
	public function getRows() {
		return $this->rows;
	}
	
	public function getThumbSettings() {
		return $this->thumb;
	}
	
	public function getImageSettings() {
		return $this->image;
	}
	
	public function getNextId() {
		return $this->nextid++;
	}
	
	public function saveSettings(SimpleXmlElement $sxml){
		$settings = $sxml->addChild('settings');
		$settings->addAttribute('cols', $this->cols);
		$settings->addAttribute('rows', $this->rows);
		$settings->addAttribute('nextid', $this->nextid);
		$thumb = $settings->addChild('thumb');
		$thumb->addAttribute('height', $this->thumb['height']);
		$thumb->addAttribute('width', $this->thumb['width']);
		$thumb->addAttribute('crop', ($this->thumb['crop'] ? 'true' : 'false'));
		$image = $settings->addChild('image');
		$image->addAttribute('height', $this->image['height']);
		$image->addAttribute('width', $this->image['width']);
		$image->addAttribute('crop', ($this->image['crop'] ? 'true' : 'false'));
	}
}

abstract class Import extends Bean{
	protected $class;
	protected $item;
	protected $itemid;
	protected $module;
	
	public function __construct($sxml, $module) {
		$this->class = (string) $sxml['class'];
		$this->itemid = (int) $sxml['item'];
		$this->module = $module;
		
		$root = $module->getRoot();
		if(isset($root))
			$this->item = $root->searchItem(array('id' => $this->itemid));
		
		if($this->item == null)
			return null;
	}
	
	public abstract function doit();
	
	public function getClass(){
		return $this->class;
	}
	
	public function getItem(){
		return $this->item;
	}
		
	public function getItemId(){
		return $this->itemid;
	}
	
	public function saveImport(SimpleXmlElement $sxml){
		$import = $sxml->addChild('import');
		$import->addAttribute('class', $this->class);
		$import->addAttribute('item', $this->itemid);
		
		return $import;
	}
}

class ImportException extends Exception { }


class GalleryAction extends Action implements ActionContainer, ProtectedAction {
	
	public static function getActions() {
		return array(
			
		);
	}
	
	public static function getRequiredPermissions() {
		return array(
			'read'
		);
	}
	
	public function execute() {
		$this->getModel()->setStorage($this->getProcessor()->getURL()->getPath());
		
		$this->getModel()->selectItem($this->getRequest()->getParameters());
		$this->getDesign()->assign('gallerypath', $this->getModel()->getGalleryPath());
		$this->getDesign()->assign('settings', $this->getModel()->getSettings());
		$this->getDesign()->assign('gallery', $this->getModel()->getItem());

		$this->getDesign()->display('Gallery/gallery.tpl');
	}
}

/* modifier actions -- DO NOT USE !!!

class GalleryAddAction extends Action implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'add'
		);
	}
	
	public function execute() {
		
		exit();
	}
}

class GalleryDeleteAction extends Action implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'delete'
		);
	}
	
	public function execute() {
		$id = (int) $this->getRequest()->getParameter('id');
		
		if($id != 0){
			$this->getModel()->selectItem($id);
			$this->getModel()->getItem()->getParent()->delete($id);
		}
		
		$this->getModel()->save();
		
		$this->forward('?id='.$this->getModel()->getItem()->getParent()->getId(), new Message('Item deleted!'));
	}
}

class GalleryMoveLeftAction extends Action implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function execute() {
		$id = (int) $this->getRequest()->getParameter('id');
		
		if($id != 0){
			$this->getModel()->selectItem($id);
			$this->getModel()->getItem()->getParent()->moveLeft($id);
		}
		
		$this->getModel()->save();
		
		$this->forward('?id='.$this->getModel()->getItem()->getParent()->getId(), new Message('Item moved!'));
	}
}

class GalleryMoveRightAction extends Action implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function execute() {
		$id = (int) $this->getRequest()->getParameter('id');
		
		if($id != 0){
			$this->getModel()->selectItem($id);
			$this->getModel()->getItem()->getParent()->moveRight($id);
		}
		
		$this->getModel()->save();
		
		$this->forward('?id='.$this->getModel()->getItem()->getParent()->getId(), new Message('Item moved!'));
	}
}

*/

class GalleryAdminAction extends AbstractAdminAction implements ActionContainer {
	
	public static function getActions() {
		return array(
			'import' => 'GalleryAdminImportAction',
			'addgallery' => 'GalleryAdminAddGalleryAction',
			'addimport' => 'GalleryAdminAddImportAction',
			'addimage' => 'GalleryAdminAddImageAction',
			'delete' => 'GalleryAdminDeleteGalleryAction',
			'edit' => 'GalleryAdminEditGalleryAction'
		);
	}
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function execute() {
		$this->getDesign()->assign('imports', $this->getModel()->getImports());
		
		$galleries = array();
		$this->getModel()->getRoot()->listGalleries(&$galleries, 0);
		
		$this->getDesign()->assign('galleries', $galleries);
		$this->getDesign()->display('Admin/Gallery/gallery.tpl');
		
	}
}



class GalleryAdminImportAction extends AbstractAdminAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function execute() {
		$parameter = $this->getRequest()->getParameters();
		
		$import = $this->getModel()->getImport($parameter['id']);
			
		if(isset($import)){
			try{
				$this->getModel()->setStorage($this->getProcessor()->getURL()->getPath());
				$import->import();
				$this->getModel()->save();
				$message = new Message($import->getItem()->getTitle().' successfully imported.');
			}
			catch(ImportException $e){
				$message = new ErrorMessage($e->getMessage());
			}
		}
		
		$this->forward('', $message);

	}

}

class GalleryAdminGalleryForm extends Form {
	public $id = 0;
	public $title = '';
	public $date = '';
	public $parentid = 0;
	
	public $parents = array();
	public $parentids = array();
	
	protected function validate() {
		
	}
}

class GalleryAdminAddGalleryAction extends AbstractAdminFormAction {

	public function getTemplate() {
		return 'Admin/Gallery/addgallery.tpl';
	}
	
	protected function getReturn() {
		return 'galleries';
	}

	public function load() {
		$this->getModel()->selectItem($this->getRequest()->getParameters());
	}
	
	protected function createForm() {
		return new GalleryAdminGalleryForm();
	}
	
	protected function loadForm(Form $form) {
		$galleries = array();
		$this->getModel()->getRoot()->listGalleries(&$galleries, 0);
		$imports = $this->getModel()->getImports();
		foreach($galleries as $gallery){
			if(!isset($imports[$gallery->getId()])){
				$form->parents[] = $gallery->getDeep().$gallery->getTitle();
				$form->parentids[] = $gallery->getId();
			}
		}
		$form->parentid = $this->getModel()->getItem()->getId();
	}
	
	public function succeed(Form $form) {	
		$date = $form->date['Year'] . '-' . $form->date['Month'] . '-' . $form->date['Day'];
		
		$this->getModel()->addGallery($form->title, $date, $form->parentid);
		$this->getModel()->save();

		return new Message('Gallery created!');
	}
}

class GalleryAdminDeleteGalleryAction extends AbstractAdminAction {
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function execute() {
		$parameter = $this->getRequest()->getParameters();
		
		$this->getModel()->selectItem($parameter);
		
		if($this->getModel()->deleteGallery())
			$message = new Message('Gallery deleted!');
		else
			$message = new Message('Can not delete Gallery!');
		
		$this->getModel()->save();
		$this->forward('', $message);

	}
}

class GalleryAdminEditGalleryAction extends AbstractAdminAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function execute() {
		$parameter = $this->getRequest()->getParameters();
		
		$this->getModel()->selectItem($parameter);
		
		$this->getDesign()->assign('item', $this->getModel()->getItem());
		
		$this->getDesign()->display('Admin/Gallery/editgallery.tpl');
		
	}
}

class GalleryAdminAddImageForm extends Form {
	public $onlineimage = '';
	public $uploadimage = '';
	
	public $parentid = 0;
	
	public $parents = array();
	public $parentids = array();
	
	protected function validate() {
		
	}
}

class GalleryAdminAddImageAction extends AbstractAdminFormAction {

	public function getTemplate() {
		return 'Admin/Gallery/addimage.tpl';
	}
	
	protected function getReturn() {
		return 'images';
	}

	public function load() {
		$this->getModel()->selectItem($this->getRequest()->getParameters());
	}
	
	protected function createForm() {
		return new GalleryAdminAddImageForm();
	}
	
	protected function loadForm(Form $form) {
		$galleries = array();
		$this->getModel()->getRoot()->listGalleries(&$galleries, 0);
		$imports = $this->getModel()->getImports();
		foreach($galleries as $gallery){
			if(!isset($imports[$gallery->getId()])){
				$form->parents[] = $gallery->getDeep().$gallery->getTitle();
				$form->parentids[] = $gallery->getId();
			}
		}
		$form->parentid = $this->getModel()->getItem()->getId();
	}
	
	public function succeed(Form $form) {
		if($form->parentid=='' || $form->parentid == 0)
			return new WarningMessage('Could not upload image to toplevelgallery.');
		if(isset($form->onlineimage) && $form->onlineimage != ''){
			$source = $form->onlineimage;
			$message = new Message('Image added!');
		}
		else{
			if($_FILES['uploadimage']['type'] != 'image/jpeg')
				return new WarningMessage('At the moment you can only upload jpeg files.');
			$folder = $this->getModel()->getMediaPath() . '/galleries/' . $form->parentid . '/';
			if(!file_exists($folder))
				mkdir($folder, 0755, true);
			if(move_uploaded_file($_FILES['uploadimage']['tmp_name'], $folder . basename($_FILES['uploadimage']['name']))) {
				$source = $folder . basename($_FILES['uploadimage']['name']);
				$message = new Message('Image uploaded!');
			}
			else {
				return new WarningMessage('Could not upload image.');
			}
		}
		$this->getModel()->addImage($source, $form->parentid);
		$this->getModel()->save();

		return $message;
	}
}

class GalleryAdminAddImportForm extends Form {
	public $id = 0;
	public $rss = '';
	public $photoset = '';
	public $parentid = 0;
	
	public $parents = array();
	public $parentids = array();
	
	protected function validate() {
		
	}
}

class GalleryAdminAddImportAction extends AbstractAdminFormAction {

	public function getTemplate() {
		return 'Admin/Gallery/addimport.tpl';
	}
	
	protected function getReturn() {
		return 'imports';
	}

	public function load() {
		$this->getModel()->selectItem($this->getRequest()->getParameters());
	}
	
	protected function createForm() {
		return new GalleryAdminAddImportForm();
	}
	
	protected function loadForm(Form $form) {
		$galleries = array();
		$this->getModel()->getRoot()->listGalleries(&$galleries, 0);
		$imports = $this->getModel()->getImports();
		foreach($galleries as $gallery){
			if(!isset($imports[$gallery->getId()])){
				$form->parents[] = $gallery->getDeep().$gallery->getTitle();
				$form->parentids[] = $gallery->getId();
			}
		}
		$form->parentid = $this->getModel()->getItem()->getId();
	}
	
	public function succeed(Form $form) {	
		if(isset($form->rss) && $form->rss != ""){
			$id = $this->getModel()->addGallery('' ,'' , $form->parentid);
			$import = simplexml_load_string('<import class="Picasa" item="'.$id.'" rss="'.$form->rss.'"/>');
			$this->getModel()->addImport($import);
			$message = new Message('Import created!');
		}
		else if(isset($form->photoset) && $form->photoset != ""){
			$id = $this->getModel()->addGallery('' ,'' , $form->parentid);
			$import = simplexml_load_string('<import class="Flickr" item="'.$id.'" photoset="'.$form->photoset.'"/>');
			$this->getModel()->addImport($import);
			$message = new Message('Import created!');
		}
		else {
			$message = new Message('Could not create import!');
		}
		$this->getModel()->save();

		return new Message('Import created!');
	}
}

?>
