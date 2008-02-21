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
 * Gallery System in anomey 2.1
 * 
 * @autor Adrian Egloff<adrian.egloff@gmail.com>
 * @date 08.11.2006
 * 
 * 
 * Last Changes:
 * - redesign of admin panel
 * - admin settingspage
 * - no image thumbnail
 * - implemented pages
 * - Thumbnail fallback with random Thumbnails
 * - Implemented the Composite Pattern for the Tree Structure
 * - Image Cach implemented
 * - Import for Picasa and Flickr Galleries
 * - Admin Import
 * 
 * 
 * Thanks to fabian who made this nice looking <ul><li>-boxes :)
 * 
 */

include_once('Beans.classes.php');
include_once('GalleryItem.class.php');
include_once('ImageItem.class.php');
include_once('ImageManipulation.classes.php');

/**
 * This is the model of the gallery module
 * 
 * It loads, holds and saves alldata when they are changed. There are some datastructure classes which are needed by the model
 * in th Beans.classes.php file.
 */
class Gallery extends Module {
	private $settings;
	private $root;
	private $item;
	private $storage = array();
	private $imports = array();
	private $sxml;
	private $state;
	
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
		
		$this->checkState();
		$this->loadSettings();
		$this->setStorage(null);
		//$this->loadImports();
		$this->loadItems();
		
		
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
	
	private function checkState(){
		$this->state = new State();
		
		// gd check
		
		$this->state->tools['gd']['available'] = GDImageManipulation::available();
		
		$this->state->tools['gd']['formats'] = GDImageManipulation::mimeTypes();
		
		
		// fake check
		$this->state->tools['fake']['available'] = FakeImageManipulation::available();
		
		$this->state->tools['fake']['formats'] = FakeImageManipulation::mimeTypes();
		
			
	}
	
	public function getState(){
		return $this->state;
	}
	
	public function checkFormat($format){
		if(isset($this->state->formats[$format]) && $this->state->formats[$format])
			return true;
		else
			return false;
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
		if(file_exists($this->getSecurity()->getProfile().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'Gallery'.DIRECTORY_SEPARATOR.$class.'.class.php'))
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
		
		// load default Picasa/Flickr imports if there is no item in the gallery
		if(!isset($this->sxml->item[0])){
			if(!isset($this->sxml->imports))
				$imports = $this->sxml->addChild('imports');
			else
				$imports = $this->sxml->imports[0];
			
			$import = $imports->addChild('import');
			$import->addAttribute('class', 'Picasa');
			$import->addAttribute('item', '10');
			$import->addAttribute('rss', 'http://picasaweb.google.com/data/feed/back_compat/user/adrian.egloff/albumid/4984938126230224913');
			
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
			
			
			$this->root = $this->loadItem(null, $root);
			
			$this->loadImports();
			
			foreach($this->imports as $import)
				$import->doit();
				
			$this->save();
			
		}

		try {
			$this->root = $this->loadItem(null, $this->sxml->item[0]);
		}
		catch (ImportException $e){
			echo "could not import";
		}
	}
	
	public function setStorage($url_path){
		$this->storage['local'] = $this->getSecurity()->getProfile().DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.$this->getId().DIRECTORY_SEPARATOR.'galleries'.DIRECTORY_SEPARATOR;
		$this->storage['cache'] = $this->getSecurity()->getProfile().DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR.$this->getId().DIRECTORY_SEPARATOR;
		$this->storage['web'] = $url_path. $this->getSecurity()->getProfile() . '/tmp/cache/'.$this->getId().'/';
		
		if(!file_exists($this->storage['cache'])){
			// create cache-folder
			mkdir($this->storage['cache']);
		}
		if(!file_exists($this->storage['local'])){
			// create media-folder
			mkdir($this->getSecurity()->getProfile().DIRECTORY_SEPARATOR.'media'.DIRECTORY_SEPARATOR.$this->getId());
			mkdir($this->storage['local']);
		}
	}
	
	public function loadItem($parentitem, SimpleXMLElement $sxml){
		$class = (string) $sxml['class'].'Item';
		if(file_exists($this->getSecurity()->getProfile().DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'Gallery'.DIRECTORY_SEPARATOR.$class.'.class.php'))
			include_once($class.'.class.php');
		
		if(class_exists($class)){
			$item = new $class($this, $parentitem, $sxml);
		
			foreach($sxml->item as $childxml){
				$item->addChild($this->loadItem($item, $childxml));
			}
		}
		return $item;
	}
	
	public function getDefaultImage(){
		return $this->getSecurity()->getProfile() . '/modules/Gallery/noimage.jpeg';
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
	
	public function prepareImage(ImageItem $item, $settings){
		
		if(GDImageManipulation::available()){
			$imageLibrary = new GDImageManipulation();
		}
		else if(FakeImageManipulation::available()){
			$imageLibrary = new FakeImageManipulation();
		}
		
		$remote = $item->getSource();
		$local = $item->getCache(null);
		
		$cache = $item->getCache($settings);
		
		if(!file_exists($local))
			copy($remote, $local);
		
		$imageLibrary->resizeImage($local, $cache, $settings['width'], $settings['height'], $settings['crop']);
		
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
		$this->sxml = $sxml;
	}
}


// Exceptions ...

/**
 * Thrown when the import and conversion of an image failed
 * 
 */
class ImportException extends Exception { }

// Actions
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
		
		$settings = $this->getModel()->getSettings();
		
		$page = $this->getRequest()->getParameter('page', 0);
		$this->getDesign()->assign('cols', $settings->getCols());
		$this->getDesign()->assign('rows', $settings->getRows());
		$this->getDesign()->assign('page', $page);
		
		$itemsperpage = $settings->getCols() * $settings->getRows();
		$start = $itemsperpage * $page;
		$end = $start + $itemsperpage;
		
		$this->getDesign()->assign('start', $start);
		$this->getDesign()->assign('end', $end);
		$this->getDesign()->assign('nextpage', $page+1);
		$this->getDesign()->assign('prevpage', $page-1);
		
		
		$this->getDesign()->assign('gallerypath', $this->getModel()->getGalleryPath());
		$this->getDesign()->assign('settings', $this->getModel()->getSettings());
		$this->getDesign()->assign('gallery', $this->getModel()->getItem());
		
		$this->display('Gallery/gallery.tpl');

	}
}

// Admin actions

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
			'content' => 'GalleryAdminContentAction',
			'settings' => 'GalleryAdminSettingsAction',
			'state' => 'GalleryAdminStateAction',
			
			'import' => 'GalleryAdminImportAction',
			'addgallery' => 'GalleryAdminAddGalleryAction',
			'addimport' => 'GalleryAdminAddImportAction',
			'addimage' => 'GalleryAdminAddImageAction',
			'delete' => 'GalleryAdminDeleteGalleryAction',
			'edit' => 'GalleryAdminEditGalleryAction'
		);
	}
	
	public function execute() {
		$this->forward('content');
	}
}

class GalleryAdminContentAction extends AbstractAdminAction {
	
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
		$this->display('Admin/Gallery/content.tpl');
	}
}

class GalleryAdminStateAction extends AbstractAdminAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function execute() {
		$this->getDesign()->assign('state', $this->getModel()->getState());
		
		$this->display('Admin/Gallery/state.tpl');
	}
}

class GalleryAdminSettingsForm extends Form {
	public $thumbwidth = 0;
	public $thumbheight = 0;
	public $thumbcrop = false;
	
	public $imagewidth = 0;
	public $imageheight = 0;
	public $imagecrop = false;
	
	public $rows = 0;
	public $cols = 0;
	
	protected function validate() {
		
	}
}

class GalleryAdminSettingsAction extends AbstractAdminFormAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function getTemplate() {
		return 'Admin/Gallery/settings.tpl';
	}

	protected function createForm() {
		return new GalleryAdminSettingsForm();
	}

	protected function loadForm(Form $form) {
		$settings = $this->getModel()->getSettings();
		$thumb = $settings->getThumbSettings();
		$image = $settings->getImageSettings();
		
		$form->thumbwidth = $thumb['width'];
		$form->thumbheight = $thumb['height'];
		$form->thumbcrop = $thumb['crop'];
		
		$form->imagewidth = $image['width'];
		$form->imageheight = $image['height'];
		$form->imagecrop = $image['crop'];
		
		$form->cols = $settings->getCols();
		$form->rows = $settings->getRows();
	}

	public function succeed(Form $form) {	
		$thumb = array();
		$thumb['width'] = $form->thumbwidth;
		$thumb['height'] = $form->thumbheight;
		$thumb['crop'] = $form->thumbcrop;
		
		$image = array();
		$image['width'] = $form->imagewidth;
		$image['height'] = $form->imageheight;
		$image['crop'] = $form->imagecrop;
		
		$settings = $this->getModel()->getSettings();
		$settings->setThumbSettings($thumb);
		$settings->setImageSettings($image);
		$settings->setCols($form->cols);
		$settings->setRows($form->rows);
		
		$this->getModel()->save();

		return new Message('Settings saved!');
	}
}


// Add a new gallery

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

// Add a new import

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
		
		$this->display('Admin/Gallery/editgallery.tpl');
		
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
			if(!$this->getModel()->checkFormat($_FILES['uploadimage']['type']))
				return new WarningMessage('Your image format is not supportet. Whatch the state-page.');
			
			$folder = $this->getModel()->getMediaPath().DIRECTORY_SEPARATOR.'galleries'.DIRECTORY_SEPARATOR.$form->parentid.DIRECTORY_SEPARATOR;
			if(!file_exists($folder))
				mkdir($folder);
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
				$import->doit();
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

?>
