<?php

class EntryNotFoundException extends Exception {

}

class News extends Module {

	private $nextId = 1;

	public function getAuthor() {
		return 'anomey team';
	}

	public function getAvailablePermissions() {
		return array (
		'read' => 'read entries',
		'edit' => 'add or change an entry',
		'delete' => 'delete entries'
		);
	}

	public function init() {
		$this->entries = new Collection();
	}

	private $entries;

	public function getAllPublications() {
		$publications = new Collection();
		foreach($this->entries as $entry) {
			if(!$publications->exists($entry->getPublication())) {
				$publications->set($entry->getPublication(), new Collection());
			}
			$publications->get($entry->getPublication())->append($entry);
		}
		$publications->krsort();
		return $publications;
	}

	public function getPublications() {
		$publications = new Collection();
		foreach($this->getAllPublications() as $publication => $entries) {
			if(time() >= $publication) {
				$publications->append($entries);
			}
		}
		return $publications;
	}

	public function getEntry($id) {
		if($this->entries->exists($id)) {
			return $this->entries->get($id);
		} else {
			throw new EntryNotFoundException();
		}
	}

	public function getLastModified() {
		if($this->getPublications()->first() != false) {
			return $this->getPublications()->first()->first()->getPublication();
		}
	}

	private $preface = '';

	public function setPreface($preface) {
		$this->preface = $preface;
	}

	public function getPreface() {
		return $this->preface;
	}

	public function load() {
		$xml = $this->getXml();
		$this->nextId = (int) $xml['nextid'];
		$this->preface = (string) $xml->preface;

		foreach ($xml->entry as $entry) {
			try {
				$author = $this->getSecurity()->getUser((string) $entry->author);
			} catch (UserNotFoundException $e) {
				$author = null;
			}
			$this->entries->set((string) $entry->id, new NewsEntry((string) $entry->id, (string) $entry->title, (string) $entry->name, (string) $entry->content, (string) $entry->publication, (string) $entry->created, $author));
		}
	}

	public function add($title, $content, $publication, $author) {
		$id = 'e'.$this->nextId++;
		$name = trim(preg_replace('/([^a-z-_])/', '', strtr(strtolower(HTML::decodeSpecialchars($title)), array(' ' => '-', '<' => '-', '>' => '-', 'ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue'))), ' -');
		$entry = new NewsEntry($id, $title, $name, $content, $publication, date('c'), $author);
		$this->entries->set($id, $entry);
	}

	public function update(NewsEntry $entry) {
		$this->entries->set($entry->getId(), $entry);
	}

	public function delete($id) {
		$this->entries->remove($id);
	}

	public function save() {
		$xml = XML :: create('news');
		$xml->addAttribute('nextid', $this->nextId);
		$xml->addChild('preface', $this->getPreface());

		$this->setModified(date('Y-m-d', $this->getLastModified()));
		$this->getSite()->save();

		foreach ($this->getAllPublications() as $publication) {
			foreach ($publication as $entry) {
				$child = $xml->addChild('entry');
				$child->addChild('id', $entry->getId());
				$child->addChild('title', HTML :: specialchars($entry->getTitle()));
				$child->addChild('name', HTML :: specialchars($entry->getName()));
				$child->addChild('content', $entry->getContent());
				$child->addChild('publication', date('c', $entry->getPublication()));
				$child->addChild('created', date('c', $entry->getCreated()));
				if($entry->getAuthor() != null) {
					$child->addChild('author', $entry->getAuthor()->getId());
				}
			}
		}

		$this->store($xml);
	}
}

class NewsEntryForm extends Form {
	public $name = '';
	public $comment = '';

	protected function validate() {
		$this->assertNotEmpty($this->name, new Message('Please type in a name!', 'error'));
		$this->assertNotEmpty($this->comment, new Message('Please type in some content!', 'error'));
	}
}

class NewsAddEntryAction extends FormAction implements ProtectedAction {

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

class NewsEntry extends Bean {
	private $id = '';
	private $title = '';
	private $name = '';
	private $content = '';
	private $publication = '';
	private $created = '';
	private $author = null;

	public function getId() {
		return $this->id;
	}

	public function getTitle() {
		return $this->title;
	}

	public function getName() {
		return $this->name;
	}

	public function getContent() {
		return $this->content;
	}

	public function getPublication() {
		return $this->publication;
	}

	public function getCreated() {
		return $this->created;
	}

	public function getAuthor() {
		return $this->author;
	}

	public function __construct($id, $title, $name, $content, $publication, $created, $author) {
		$this->id = $id;
		$this->title = $title;
		$this->name = $name;
		$this->content = $content;
		$this->publication = strtotime($publication);
		$this->created = strtotime($created);
		$this->author = $author;
	}
}

class NewsAction extends Action implements ProtectedAction, ActionContainer {

	public static function getActions() {
		return array(
		'feed' => 'NewsFeedAction'
		);
	}

	public static function getRequiredPermissions() {
		return array (
		'read'
		);
	}

	public function execute() {
		$this->display('News/entries.tpl');
	}
}

class NewsFeedAction extends Action implements ProtectedAction {

	public static function getRequiredPermissions() {
		return array (
		'read'
		);
	}

	public function execute() {
		$this->getResponse()->setContentType('application/xml');
		$this->display('News/atom.tpl');
	}
}

class NewsAdminAction extends AbstractAdminAction implements ActionContainer {

	public static function getActions() {
		return array (
		'entries' => 'NewsAdminEntriesAction',
		'settings' => 'NewsAdminSettingsAction'
		);
	}

	public function execute() {
		$this->forward('entries');
	}
}

class NewsAdminForm extends Form {

	public $toDelete = array();

	public function validate() {
		$this->assertTrue(count($this->toDelete) > 0, new ErrorMessage('Please select at least one entry to delete.'));
	}
}

class NewsAdminEntriesAction extends AbstractAdminFormAction implements ActionContainer {

	public static function getActions() {
		return array (
		'edit' => 'NewsAdminEditEntryAction',
		'add' => 'NewsAdminAddEntryAction'
		);
	}

	public function getTemplate() {
		return 'Admin/News/entries.tpl';
	}

	public function load() {
		$this->getDesign()->assign('publications', $this->getModel()->getAllPublications());
	}

	public function createForm() {
		return new NewsAdminForm();
	}

	public function succeed(Form $form) {
		foreach($form->toDelete as $id) {
			try {
				$this->getModel()->delete($id);
			} catch(PageNotFoundException $e) {
				// ignore
			}
		}
		$this->getModel()->save();

		return new Message('Selected entries(s) deleted.');
	}
}

class NewsAdminEntryForm extends Form {

	public $title = '';
	public $publicationDate = '';
	public $publicationTime = '';
	public $contentOfEntry = '';

	protected function validate() {

	}
}

class NewsAdminEditEntryAction extends AbstractAdminFormAction {

	public function getTemplate() {
		return 'Admin/News/edit.tpl';
	}

	protected function getReturn() {
		return 'entries';
	}

	private $entry;

	public function load() {
		$this->entry = $this->getModel()->getEntry($this->getRequest()->getParameter(6));
	}

	protected function createForm() {
		return new NewsAdminEntryForm();
	}

	protected function loadForm(Form $form) {
		$form->title = $this->entry->getTitle();
		$form->publicationDate = $this->entry->getPublication();
		$form->publicationTime = $this->entry->getPublication();
		$form->contentOfEntry = $this->entry->getContent();
	}

	public function succeed(Form $form) {
		$publication = $form->publicationDate['Year'] . '-' . $form->publicationDate['Month'] . '-' . $form->publicationDate['Day'] . 'T' . $form->publicationTime['Hour'] . ':' . $form->publicationTime['Minute'] . ':00';

		$entry = new NewsEntry($this->entry->getId(), $form->title, $this->entry->getName(), $form->contentOfEntry, $publication, $this->entry->getCreated(), $this->getRequest()->getUser());
		$this->getModel()->update($entry);
		$this->getModel()->save();

		return new Message('Changes on entry saved!');
	}
}

class NewsAdminAddEntryAction extends AbstractAdminFormAction {

	public function getTemplate() {
		return 'Admin/News/add.tpl';
	}

	protected function getReturn() {
		return 'entries';
	}

	protected function createForm() {
		return new NewsAdminEntryForm();
	}

	public function succeed(Form $form) {
		$publication = $form->publicationDate['Year'] . '-' . $form->publicationDate['Month'] . '-' . $form->publicationDate['Day'] . 'T' . $form->publicationTime['Hour'] . ':' . $form->publicationTime['Minute'] . ':00';

		$this->getModel()->add($form->title, $form->contentOfEntry, $publication, $this->getRequest()->getUser());
		$this->getModel()->save();

		return new Message('Entry created!');
	}
}

class NewsAdminSettingsForm extends AbstractAdminForm {
	public $preface = '';
}

class NewsAdminSettingsAction extends AbstractDefaultAdminFormAction {

	public static function getRequiredPermissions() {
		return array(
		'edit'
		);
	}

	public function getTemplate() {
		return 'Admin/News/settings.tpl';
	}

	protected function getReturn() {
		return 'settings';
	}

	protected function loadAdminForm(Form $form) {
		$form->preface = $this->getModel()->getPreface();
	}

	protected function createAdminForm() {
		return new NewsAdminSettingsForm();
	}

	public function save(Form $form) {
		$this->getModel()->setPreface($form->preface);
		$this->getModel()->save();

		return new Message('Settings saved!');
	}
}

?>
