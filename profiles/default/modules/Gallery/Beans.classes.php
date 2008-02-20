<?php

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
	
	public function setCols($cols) {
		$this->cols = $cols;
	}
	
	public function setRows($rows) {
		$this->rows = $rows;
	}
	
	public function getThumbSettings() {
		return $this->thumb;
	}
	
	public function getImageSettings() {
		return $this->image;
	}
	
	public function setThumbSettings($thumb) {
		$this->thumb = $thumb;
	}
	
	public function setImageSettings($image) {
		$this->image = $image;
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

class State extends Bean {
	public $formats = array();
	public $tools = array();
	
	public function getTools(){
		return $this->tools;
	}
	
	public function getFormats(){
		return $this->formats;
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

abstract class Component extends Bean {
	protected $module;
	protected $parent;
	
	protected $class;
	protected $id;
	protected $title;
	protected $date;
	
	
	protected $sxml;
	
	public function __construct(Gallery $module, $parent, SimpleXMLElement $sxml){
		$this->module = $module;
		if($parent)
			$this->parent = $parent;
		
		if($sxml['class'])
			$this->class = (string) $sxml['class'];
		if($sxml['id'])
			$this->id = (int) $sxml['id'];
		if($sxml['title'])
			$this->title = (string) $sxml['title'];
		if($sxml['date'])
			$this->date = (string) $sxml['date'];
		
		$this->sxml = $sxml;
	}
	
	public function getParent(){
		return $this->parent;
	}
	
	public function getId(){
		return $this->id;
	}
	
	public function getTitle(){
		return $this->title;
	}
	
	public function getDate(){
		return $this->date;
	}
	
	// returns the class (image, gallery, movie, ...) 
	public function getClass(){
		return $this->class;
	}
	
	// "Search Item by Parameter"-function
	// returns the item maching the $parameter array
	public function searchItem($parameter){
		if(isset($parameter['id']) && $this->id == $parameter['id']){
			return $this;
		}
		else{
			return null;
		}
	}
	
	// each item needs a thumbnail
	public abstract function getThumb();
	
	public function saveItem(SimpleXMLElement $sxml){
		$item = $sxml->addChild('item');
	
		if($this->class)
			$item->addAttribute('class', $this->class);
		if($this->id)
			$item->addAttribute('id', $this->id);
		if($this->title)
			$item->addAttribute('title', $this->title);
		if($this->date)
			$item->addAttribute('date', $this->date);

		return $item;
		
	}
	
	// modifier functions -- DO NOT USE !!!
	/*
	public function moveLeft(){
		if($this->parent != null){
			$this->parent->moveLeft($this->id);
		}
	}
	
	public function moveRight(){
		if($this->parent != null){
			$this->parent->moveRight($this->id);
		}
	}
	
	public function delete(){
		if($this->parent != null){
			$this->parent->delete($this->id);
		}
	}*/
	
}

abstract class Composite extends Component {
	protected $children = array();
	private $deep;
	
	public function __construct(Gallery $module, $parent, SimpleXMLElement $sxml){
		parent::__construct($module, $parent, $sxml);
		
	}
	
	public function getChildren(){
		return $this->children;
	}
	
	public function getChildrenSize(){
		return count($this->children);
	}
	
	public function searchItem($parameter){
		if(isset($parameter['id']) && $this->id == $parameter['id']){
			return $this;
		}
		else {
			foreach($this->children as $child){
				if(($item = $child->searchItem($parameter)) != null){
					return $item;
				}
			}
		}
		return null;
	}
	
	public function addChild($child){
		$this->children[] = $child;
	}
	
	public function deleteChildren(){
		$this->children = array();
	}
	
	public function saveItem(SimpleXMLElement $sxml){
		$item = parent::saveItem($sxml);
		foreach($this->children as $child){
			$child->saveItem($item);
		}
		return $item;
	}
	
	public function listGalleries($galleries, $deep){
		$this->deep = $deep;
		$galleries[] = $this;
		foreach($this->children as $child){
			if($child instanceof Composite){
				$child->listGalleries(&$galleries, $deep+1);
			}
		}
	}
	
	public function getDeep(){
		$deep = '';
		for($i=0; $i<$this->deep; $i++){
			$deep=$deep.'&nbsp;&nbsp;';
		}
		return $deep;
	}
	
	public function deleteItem($id){
		$children = array();
		for($i = 0; $i<count($this->children); $i++){
			if($this->children[$i]->getId() != $id){
				$children[] = $this->children[$i];
			}
		}
		$this->children = $children;
	}
	
	
	// modifier funcitons -- DO NOT USE !!!
	/*
	public function moveRight($id){
		for($i = 0; $i<count($this->children) - 1; $i++){
			if($this->children[$i]->getId() == $id){
				$child = $this->children[$i];
				$this->children[$i] = $this->children[$i + 1];
				$this->children[$i + 1] = $child;
				$i = count($this->children);
			}
		}
	}
	
	public function moveLeft($id){
		for($i = 1; $i<count($this->children); $i++){
			if($this->children[$i]->getId() == $id){
				$child = $this->children[$i];
				$this->children[$i] = $this->children[$i - 1];
				$this->children[$i - 1] = $child;
				$i = count($this->children);
			}
		}
	}
	
	*/
	
}
	
	
abstract class Leaf extends Component {

	public function __construct(Gallery $module, $parent, SimpleXMLElement $sxml){
		parent::__construct($module, $parent, $sxml);
	}
	
	public abstract function getItem();
	
	public function saveItem(SimpleXMLElement $sxml){
		$item = parent::saveItem($sxml);
		return $item;
	}
}

?>