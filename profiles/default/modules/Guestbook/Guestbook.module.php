<?php
class Guestbook extends Module {
	
	public function getAuthor() {
		return 'anomey team';
	}

	public function getAvailablePermissions() {
		return array (
			'read' => 'read entries of guestbook',
			'add' => 'add a new entry to the guestbook',
			'edit' => 'change entry of the guestbook',
			'delete' => 'delete an entry of the guestbook'
		);
	}

	private $entries = array ();

	public function getEntries() {
		return $this->entries;
	}

	public function load() {
		foreach ($this->getXml()->entry as $entry) {
			$this->entries[] = new Entry((string) $entry->name, (string) $entry->content);
		}
	}

	public function add(Entry $entry) {
		$this->entries = array_merge(array (
			$entry
		), $this->getEntries());
	}

	public function save() {
		$xml = XML :: create('guestbook');
		foreach ($this->getEntries() as $entry) {
			$child = $xml->addChild('entry');
			$child->addChild('name', HTML :: specialchars($entry->getName()));
			$child->addChild('content', HTML :: specialchars($entry->getComment()));
		}

		$this->store($xml);
	}
}

class NewEntryForm extends Form {
	public $name = '';
	public $comment = '';

	protected function validate() {
		$this->assertNotEmpty($this->name, new Message('Please type in a nams!', 'error'));
		$this->assertNotEmpty($this->comment, new Message('Please type in some content!', 'error'));
	}
}

class GuestbookAddAction extends FormAction implements ProtectedAction {

	public static function getRequiredPermissions() {
		return array (
			'add'
		);
	}

	protected function getTemplate() {
		return 'Guestbook/add.tpl';
	}

	protected function getReturn() {
		return '';
	}

	protected function createForm() {
		return new NewEntryForm();
	}

	protected function succeed(Form $form) {
		$entry = new Entry($form->name, $form->comment);
		$this->getModel()->add($entry);
		$this->getModel()->save();

		return new Message('Entry added!');
	}
}

class GuestbookDeleteAction extends Action {
	public function execute() {
		$id = $this->getRequest()->getParameter('id');
		$this->getModel()->delete($id);
		$this->forward('', new Message('Entry deleted!'));
	}
}

class Entry extends Bean {
	private $name = '';
	private $comment = '';

	public function getName() {
		return $this->name;
	}

	public function getComment() {
		return $this->comment;
	}

	public function __construct($name, $comment) {
		$this->name = $name;
		$this->comment = $comment;
	}
}

class GuestbookAction extends Action implements ActionContainer, ProtectedAction {

	public static function getActions() {
		return array (
			'add' => 'GuestbookAddAction'
		);
	}

	public static function getRequiredPermissions() {
		return array (
			'read'
		);
	}

	public function execute() {
		$this->getDesign()->assign('entries', $this->getModel()->getEntries());
		$this->display('Guestbook/entries.tpl');
	}
}
?>
