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

class AdminPagesForm extends Form {

	public $toDelete = array();

	public function validate() {
		$this->assertTrue(count($this->toDelete) > 0, new ErrorMessage('Please select at least one page to delete.'));
	}
}

class AdminPagesAction extends AdminBaseFormAction implements ActionContainer {

	public static function getActions() {
		return array (
			'o[0-9]*' => 'AdminPageEditAction',
			'up' => 'AdminPageUpAction',
			'down' => 'AdminPageDownAction',
			'new' => 'AdminPageCreateNewAction'
		);
	}
	
	public function getTemplate() {
		return 'Admin/pages.tpl';
	}
	
	public function load() {
		$this->getDesign()->assign('pages', $this->getModel()->getChilds());
	}
	
	public function createForm() {
		return new AdminPagesForm();
	}
	
	public function succeed(Form $form) {
		foreach($form->toDelete as $id) {
			try {
				$page = $this->getModel()->getPage($id);
				$this->getModel()->deletePage($page);
			} catch(PageNotFoundException $e) {
				// ignore
			}
		}
		$this->getModel()->save();
		
		return new Message('Selected page(s) deleted.');
	}
}

abstract class AbstractAdminForm extends Form {
	public $title = '';
	public $name = '';
	public $parent = '';
	public $display = '';

	private $elements;

	public function getElements() {
		return $this->elements;
	}

	public $id; //TODO should be private (get-/setter)

	public $neighbours = array(); // TODO should be private (get-/setter)
	
	private $site;
	
	public function setSite(Site $site) {
		$this->site = $site;
	}
	
	public function buildTree(Model $model, $count = 0) {
		if ($model->getId() != $this->id) {
			if($model instanceof Site) {
				$name = 'site';
			} elseif ($model instanceof Module) {
				$name = $model->getName();
			} else {
				$name = 'unknown';
			}
			$this->elements[$model->getId()] = str_repeat('&nbsp;&nbsp;', $count) . ' - ' . $name;
			foreach ($model->getChilds() as $child) {
					$this->buildTree($child, $count +1);
			}
		}
	}

	public function validate() {
		$this->assertTrue($this->site->canSave(), new ErrorMessage('Can\'t save settings. Please check file permissions.'));
		$this->assertNotEmpty($this->title, new ErrorMessage('Please type in a title.'));
		if ($this->assertNotEmpty($this->name, new ErrorMessage('Please type in a name.'))) {
			if ($this->assertRegEx($this->name, '/^' . URI::CHARS . '*$/', new ErrorMessage('The name may consists only of letters, digits, hyphens and underlines.'))) {
				$this->assertNotInList($this->name, $this->neighbours, new ErrorMessage('This name is already in use.'));
			}
		}
	}
}

abstract class AbstractAdminAction extends Action implements ProtectedAction, SecureAction {
	
	public static function getRequiredPermissions() {
		return array('edit');
	}
	
	protected function getBase() {
		return '/admin/pages/' . $this->getModel()->getId();
	}
}


abstract class AbstractAdminFormAction extends FormAction implements ProtectedAction, SecureAction {
	
	public static function getRequiredPermissions() {
		return array('edit');
	}
	
	protected function getBase() {
		return '/admin/pages/' . $this->getModel()->getId();
	}
}

abstract class AbstractDefaultAdminFormAction extends AbstractAdminFormAction implements ProtectedAction, SecureAction {
	
	public static function getRequiredPermissions() {
		return array('edit');
	}
	
	protected function getBase() {
		return '/admin/pages/' . $this->getModel()->getId();
	}
	
	protected function getReturn() {
		return '/admin/pages';
	}
	

	protected function createForm() {
		$form = $this->createAdminForm();
		$form->id = $this->getModel()->getId();
		$neighbours = array ();
		foreach ($this->getModel()->getParent()->getChilds() as $neighbour) {
			if ($neighbour->getId() != $this->getModel()->getId()) {
				$neighbours[] = $neighbour->getName();
			}
		}
		$form->neighbours = $neighbours;
		$form->buildTree($this->getModel()->getSite());
		$form->setSite($this->getModel()->getSite());
		
		return $form;
	}
	
	abstract protected function createAdminForm();

	protected function loadForm(AbstractAdminForm $form) {
		$form->title = $this->getModel()->getTitle();
		$form->name = $this->getModel()->getName();
		$form->parent = $this->getModel()->getParent()->getId();
		if ($this->getModel()->getHide()) {
			$form->display = 'hide';
		} else {
			$form->display = 'show';
		}
		$form->contentOfPage = $this->getModel()->getXML()->asXML();
		
		$this->loadAdminForm($form);
	}
	
	protected function loadAdminForm(Form $form) {
		
	}

	protected function succeed(Form $form) {
		$this->getModel()->setTitle($form->title);
		$this->getModel()->setName($form->name);

		$site = $this->getModel()->getSite();
		try {
			$parent = $site->getPage($form->parent);
			$this->getModel()->setParent($parent);
		} catch (PageNotFoundException $e) {
			$this->getRequest()->addMessage(new WarningMessage('The selected parent page could not be found.'));
		}

		if ($form->display == 'hide') {
			$this->getModel()->setHide(true);
		} else {
			$this->getModel()->setHide(false);
		}
		
		$site->save();
		
		return $this->save($form);
	}
	
	abstract protected function save(Form $form);
}

class DefaultAdminForm extends AbstractAdminForm {
	public $contentOfPage = '';
}

class DefaultAdminAction extends AbstractDefaultAdminFormAction implements ProtectedAction, SecureAction {
	
	public function getTemplate() {
		return 'Admin/default.tpl';
	}

	protected function createAdminForm() {
		return new DefaultAdminForm();
	}

	public function save(Form $form) {
		$this->getModel()->store(XML :: import(HTML :: decodeSpecialchars($form->contentOfPage)));
		$this->getModel()->setModifiedNow();

		return new Message('Changes saved!');
	}
}

class AdminPageEditAction extends AdminBaseAction {
	public function execute() {
		try {
			$container = new LinkContainer();
	
			$module = $this->getModel()->getPage($this->getRequest()->getParameter(0));
			$actionClass = get_class($module) . 'AdminAction';
			if (class_exists($actionClass)) {
				$class = new ReflectionClass($actionClass);
				$adminLink = new SecureLink($module, $module->getTitle(), $module->getHide(), $actionClass);
				Processor :: parseAction($module, $adminLink);
			} else {
				$adminLink = new SecureLink($module, $module->getTitle(), true, 'DefaultAdminAction');
				$adminLink->addRequiredPermission('edit');
				$adminLink->setSecurity('high');
			}
			
			$container->add('.*', $adminLink);
			$trail = substr($this->getRequest()->getTrail(), strlen('/admin/pages'));
			$this->getProcessor()->callAction($container, $this->getRequest(), $this->getResponse(), $trail, $this->getSecurity());
		} catch (PageNotFoundException $e) {
			$this->forward('/admin/pages', new ErrorMessage('Page does not exist!'));
		}
	}
}

class AdminPageUpAction extends AdminBaseAction {
	public function execute() {
		$page = $this->getModel()->getPage($this->getRequest()->getParameter('page'));
		$page->getParent()->moveChildUp($page->getName());
		$this->getModel()->save();

		$this->forward('/admin/pages', new Message('Page moved up!'));
	}
}

class AdminPageDownAction extends AdminBaseAction {
	public function execute() {
		$page = $this->getModel()->getPage($this->getRequest()->getParameter('page'));
		$page->getParent()->moveChildDown($page->getName());
		$this->getModel()->save();

		$this->forward('/admin/pages', new Message('Page moved down!'));
	}
}

class AdminPageCreateNewForm extends Form {

	public $title = '';
	public $name = '';
	public $parent = '';
	public $display = 'show';
	public $type = '';

	private $elements;

	public function getElements() {
		return $this->elements;
	}

	private $types;

	public function getTypes() {
		return $this->types;
	}

	private $site;

	public function __construct(Site $site) {
		$this->site = $site;
		$this->elements[$this->site->getId()] = ' - site';
		$this->buildTree($this->site);
		$this->types = $this->site->getAvaibleModules();
	}

	private function buildTree(Model $model, $count = 1) {
		foreach ($model->getChilds() as $child) {
			$this->elements[$child->getId()] = str_repeat('&nbsp;&nbsp;', $count) . ' - ' . $child->getName();
			$this->buildTree($child, $count +1);
		}
	}

	protected function validate() {
		$this->assertNotEmpty($this->title, new ErrorMessage('Please type in a title.'));
		if ($this->assertNotEmpty($this->name, new ErrorMessage('Please type in a name.'))) {
			if ($this->assertRegEx($this->name, '/^' . URI::CHARS . '*$/', new ErrorMessage('The name may consists only of letters, digits, hyphens and underlines.'))) {
				try {
					$parent = $this->site->getPage($this->parent);
				} catch (PageNotFoundException $e) {
					$this->parent = $this->site->getId();
					$parent = $this->site->getPage($this->parent);
					$this->getRequest()->addMessage(new WarningMessage('The selected parent page could not be found.'));
				}

				$neighbours = array ();
				foreach ($parent->getChilds() as $neighbour) {
					$neighbours[] = $neighbour->getName();
				}

				$this->assertNotInList($this->name, $neighbours, new ErrorMessage('The name is already in use.'));
			}
		}
		$this->assertInList($this->type, $this->types, new ErrorMessage('Unknown type.'));
	}
}

class AdminPageCreateNewAction extends AdminBaseFormAction {
	public function getTemplate() {
		return 'Admin/new.tpl';
	}

	// TODO return to just created page
	protected function getReturn() {
		return '/admin/pages';
	}

	protected function createForm() {
		$form = new AdminPageCreateNewForm($this->getModel());

		return $form;
	}

	public function succeed(Form $form) {
		$parent = $this->getModel()->getPage($form->parent);
		$page = $this->getModel()->createPage($form->title, $form->name, $parent, $form->type, $form->display == 'show' ? false : true, $this->getRequest()->getUser());
		$page->save();

		$this->getModel()->save();

		return new Message('Page created!');
	}
}

?>