<?php

abstract class Component extends Bean {
	protected $module;
	protected $parent;
	
	protected $class;
	protected $id;
	protected $title;
	protected $date;
	
	
	protected $sxml;
	
	public function __construct($module, $parent, SimpleXMLElement $sxml){
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
	
	public function __construct($module, $parent, SimpleXMLElement $sxml){
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

	public function __construct($module, $parent, SimpleXMLElement $sxml){
		parent::__construct($module, $parent, $sxml);
	}
	
	public abstract function getItem();
	
	public function saveItem(SimpleXMLElement $sxml){
		$item = parent::saveItem($sxml);
		return $item;
	}
}

?>