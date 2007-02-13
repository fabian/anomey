<?php

class People extends Module {

	const DISPLAY_LIST = 'list';

	const DISPLAY_DETAIL = 'detail';
	
	const ORDER_NICK = 'nick';
	
	const ORDER_LASTNAME = 'lastname';
	
	const ORDER_PRENAME = 'prename';
	
	const ORDER_RANDOM = 'random';

	private $display = self::DISPLAY_LIST;

	private $order = self::ORDER_LASTNAME;
	
	private $preface = '';

	public function getPeople() {
		$users = array();
		foreach($this->getSecurity()->getUsers() as $user) {
			switch($this->getOrder()) {
				case self::ORDER_NICK:
					$order = $user->getNick();
					break;
				case self::ORDER_PRENAME:
					$order = $user->getPrename();
					break;
				default:
					$order = $user->getLastname();
			}
			$users[$order . $user->getId()] = $user;
		}
		if($this->getOrder() == self::ORDER_RANDOM) {
			shuffle($users);
		} else {
			ksort($users);
		}
		return $users;
	}

	public function getDisplay() {
		return $this->display;
	}
	
	public function setDisplay($display) {
		$this->display = $display;
	}

	public function getOrder() {
		return $this->order;
	}
	
	public function setOrder($order) {
		$this->order = $order;
	}
	
	public function setPreface($preface) {
		$this->preface = $preface;
	}

	public function getPreface() {
		return $this->preface;
	}

	public function getAvailablePermissions() {
		return array (
			'read' => 'list people',
			'edit' => 'change settings'
			);
	}

	public function load() {
		$xml = $this->getXml();
		$this->display = (string) $xml->display;
		$this->order = (string) $xml->order;
		$this->preface = (string) $xml->preface;
	}

	public function save() {
		$xml = XML :: create('people');
		$xml->addChild('display', $this->display);
		$xml->addChild('order', $this->order);
		$xml->addChild('preface', $this->getPreface());
		$this->store($xml);
	}
}

class PeopleAction extends Action implements ProtectedAction {

	public static function getRequiredPermissions() {
		return array (
			'read'
			);
	}

	public function execute() {
		if($this->getModel()->getDisplay() == People::DISPLAY_DETAIL) {
			$this->getDesign()->display('People/detail.tpl');
		} else {
			$this->getDesign()->display('People/list.tpl');
		}
	}
}

class PeopleAdminForm extends AbstractAdminForm {
	
	public $displayMode = '';
	
	public $order = '';

	public $preface = '';

	public function getDisplayModes() {
		return array(
			People::DISPLAY_LIST => 'List',
			People::DISPLAY_DETAIL => 'Detail'
		);
	}

	public function getOrderAttributes() {
		return array(
			People::ORDER_NICK => 'Username',
			People::ORDER_LASTNAME => 'Lastname',
			People::ORDER_PRENAME => 'Prename',
			People::ORDER_RANDOM => 'Random'
		);
	}
	
	public function validate() {
		parent::validate();
		$this->assertInList($this->displayMode, array_keys($this->getDisplayModes()), new ErrorMessage('Unknown display mode.'));
		$this->assertInList($this->order, array_keys($this->getOrderAttributes()), new ErrorMessage('Unknown order attribute mode.'));
	}
}

class PeopleAdminAction extends AbstractDefaultAdminFormAction implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function getTemplate() {
		return 'Admin/People/people.tpl';
	}

	protected function createAdminForm() {
		return new PeopleAdminForm();
	}
	
	protected function loadAdminForm(Form $form) {
		$form->displayMode = $this->getModel()->getDisplay();
		$form->order = $this->getModel()->getOrder();
		$form->preface = $this->getModel()->getPreface();
	}

	public function save(Form $form) {
		$this->getModel()->setDisplay($form->displayMode);
		$this->getModel()->setOrder($form->order);
		$this->getModel()->setPreface($form->preface);
		$this->getModel()->save();
		
		$this->getModel()->setModifiedNow();
		$this->getModel()->getSite()->save();
		
		return new Message('Changes on page saved!');
	}
}

?>