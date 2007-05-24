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

class AdminSecurityAction extends AdminBaseAction implements ActionContainer {

	public static function getActions() {
		return array (
		'users' => 'AdminSecurityUsersAction',
		'groups' => 'AdminSecurityGroupsAction',
		'permissions' => 'AdminSecurityPermissionsAction'
		);
	}

	public function execute() {
		$this->forward('/admin/security/permissions');
	}
}

class AdminSecurityUsersForm extends Form {

	public $toDelete = array();

	public function validate() {
		$this->assertTrue(count($this->toDelete) > 0, new ErrorMessage('Please select at least one user to delete.'));
	}
}

class AdminSecurityUsersAction extends AdminBaseFormAction implements ActionContainer {

	public static function getActions() {
		return array (
		'edit' => 'AdminSecurityEditUserAction',
		'add' => 'AdminSecurityAddUserAction'
		);
	}

	public function getTemplate() {
		return 'Admin/users.tpl';
	}

	public function load() {
		$this->getDesign()->assign('users', $this->getSecurity()->getUsers());
	}

	public function createForm() {
		return new AdminSecurityUsersForm();
	}

	public function succeed(Form $form) {
		foreach($form->toDelete as $id) {
			try {
				$user = $this->getSecurity()->getUser($id);
				$this->getSecurity()->removeUser($user->getId());
				$this->getSecurity()->saveUsers();
			} catch(UserNotFoundException $e) {
				// ignore
			}
		}

		return new Message('Selected user(s) deleted!');
	}
}

class AdminSecurityUserForm extends Form {
	public $nick = '';
	public $mail = '';
	public $prename = '';
	public $lastname = '';
	public $title = '';
	public $url = '';
	public $newPassword = '';
	public $newPasswordRepeat = '';

	private $reservedNicks;

	public function __construct($reservedNicks) {
		$this->reservedNicks = $reservedNicks;
	}

	public function validate() {
		if ($this->assertNotEmpty($this->nick, new ErrorMessage('Please type in a username.'))) {
			$this->assertNotInList($this->nick, $this->reservedNicks, new ErrorMessage('The username is already used by another user.'));
		}

		if ($this->newPassword != '') {
			if ($this->assertNotEmpty($this->newPasswordRepeat, new ErrorMessage('Please type in the password twice.'))) {
				$this->assertEqual($this->newPassword, $this->newPasswordRepeat, new ErrorMessage('The password and the password repeat didn\'t match.'));
			}
		}

		if ($this->url != '') {
			$this->assertRegEx($this->url, '/^([A-Za-z][A-Za-z0-9+.-]{1,120}:[A-Za-z0-9\/](([A-Za-z0-9$_.+!*,;\/?:@&~=-])|%[A-Fa-f0-9]{2}){1,333}(#([a-zA-Z0-9][a-zA-Z0-9$_.+!*,;\/?:@&~=%-]{0,1000}))?)$/', new ErrorMessage('Please type in a valid URL or let this field empty!'));
		}
	}
}

class AdminSecurityEditUserAction extends AdminBaseFormAction {

	public function getTemplate() {
		return 'Admin/editUser.tpl';
	}

	protected function getReturn() {
		return '/admin/security/users';
	}

	private $user;

	public function load() {
		$this->user = $this->getSecurity()->getUser($this->getRequest()->getParameter(1));
	}

	protected function createForm() {
		$reservedNicks = array ();
		foreach ($this->getSecurity()->getUsers() as $user) {
			if ($user->getNick() != $this->user->getNick()) {
				$reservedNicks[] = $user->getNick();
			}
		}

		$form = new AdminSecurityUserForm($reservedNicks);

		return $form;
	}

	protected function loadForm(Form $form) {
		$form->nick = $this->user->getNick();
		$form->mail = $this->user->getMail();
		$form->prename = $this->user->getPrename();
		$form->lastname = $this->user->getLastname();
		$form->title = $this->user->getTitle();
		$form->url = $this->user->getUrl();
	}

	public function succeed(Form $form) {
		$this->user->setNick($form->nick);
		$this->user->setMail($form->mail);
		$this->user->setPrename($form->prename);
		$this->user->setLastname($form->lastname);
		$this->user->setTitle($form->title);
		$this->user->setUrl($form->url);
		if ($form->newPassword != '') {
			$this->user->setPassword(User :: password($form->newPassword));
		}
		$this->getSecurity()->saveUsers();

		return new Message('Changes on user saved!');
	}
}

class AdminSecurityAddUserForm extends AdminSecurityUserForm {

	public function validate() {
		parent :: validate();
		$this->assertNotEmpty($this->newPassword, new ErrorMessage('Please type in a password.'));
	}
}

class AdminSecurityAddUserAction extends AdminBaseFormAction {

	public function getTemplate() {
		return 'Admin/addUser.tpl';
	}

	protected function getReturn() {
		return '/admin/security/users';
	}

	protected function createForm() {
		$reservedNicks = array ();
		foreach ($this->getSecurity()->getUsers() as $user) {
			$reservedNicks[] = $user->getNick();
		}

		$form = new AdminSecurityAddUserForm($reservedNicks);

		return $form;
	}

	public function succeed(Form $form) {
		$user = new User();
		$user->setNick($form->nick);
		$user->setMail($form->mail);
		$user->setPrename($form->prename);
		$user->setLastname($form->lastname);
		$user->setPassword(User :: password($form->newPassword));
		$this->getSecurity()->createUser($user);
		$this->getSecurity()->saveUsers();

		return new Message('User has been added!');
	}
}

class AdminSecurityGroupsForm extends Form {

	public $toDelete = array();

	public function validate() {
		$this->assertTrue(count($this->toDelete) > 0, new ErrorMessage('Please select at least one group to delete.'));
	}
}

class AdminSecurityGroupsAction extends AdminBaseFormAction implements ActionContainer {

	public static function getActions() {
		return array (
		'edit' => 'AdminSecurityEditGroupAction',
		'add' => 'AdminSecurityAddGroupAction'
		);
	}

	public function getTemplate() {
		return 'Admin/groups.tpl';
	}

	public function load() {
		$this->getDesign()->assign('groups', $this->getSecurity()->getGroups());;
	}

	public function createForm() {
		return new AdminSecurityGroupsForm();
	}

	public function succeed(Form $form) {
		foreach($form->toDelete as $id) {
			try {
				$group = $this->getSecurity()->getGroup($id);
				$this->getSecurity()->removeGroup($group->getId());
				$this->getSecurity()->saveGroups();
			} catch(GroupNotFoundException $e) {
				// ignore
			}
		}

		return new Message('Selected group(s) deleted!');
	}
}

class AdminSecurityGroupForm extends Form {
	public $name = '';
	public $users = array ();

	private $allUsers = array ();

	public function getAllUsers() {
		return $this->allUsers;
	}

	private $reservedNames;

	public function __construct($allUsers, $reservedNames) {
		$this->allUsers = $allUsers;
		$this->reservedNames = $reservedNames;
	}

	public function validate() {
		$this->assertNotEmpty($this->name, new ErrorMessage('Please type in a name for the group.'));
		$this->assertNotInList($this->name, $this->reservedNames, new ErrorMessage('There is already a group with this name.'));
	}
}

class AdminSecurityEditGroupAction extends AdminBaseFormAction {

	public function getTemplate() {
		return 'Admin/editGroup.tpl';
	}

	protected function getReturn() {
		return '/admin/security/groups';
	}

	private $group;

	public function load() {
		$this->group = $this->getSecurity()->getGroup($this->getRequest()->getParameter(1));
	}

	protected function createForm() {
		$allUsers = array ();
		foreach ($this->getSecurity()->getUsers() as $user) {
			$allUsers[$user->getId()] = $user->getNick();
		}

		$reservedNames = array ();
		foreach ($this->getSecurity()->getGroups() as $group) {
			if ($this->group->getId() != $group->getId()) {
				$reservedNames[] = $group->getName();
			}
		}

		$form = new AdminSecurityGroupForm($allUsers, $reservedNames);

		return $form;
	}

	protected function loadForm(Form $form) {
		$form->name = $this->group->getName();

		$users = array ();
		foreach ($this->group->getUsers() as $user) {
			$users[] = $user->getId();
		}

		$form->users = $users;
	}

	public function succeed(Form $form) {
		$this->group->setName($form->name);
		$this->group->clearUsers();

		foreach ($form->users as $user) {
			$this->group->addUser($this->getSecurity()->getUser($user));
		}

		$this->getSecurity()->saveGroups();

		return new Message('Changes on group saved!');
	}
}

class AdminSecurityAddGroupAction extends AdminBaseFormAction {

	public function getTemplate() {
		return 'Admin/addGroup.tpl';
	}

	protected function getReturn() {
		return '/admin/security/groups';
	}

	protected function createForm() {
		$allUsers = array ();
		foreach ($this->getSecurity()->getUsers() as $user) {
			$allUsers[$user->getId()] = $user->getNick();
		}

		$reservedNames = array ();
		foreach ($this->getSecurity()->getGroups() as $group) {
			$reservedNames[] = $group->getName();
		}

		$form = new AdminSecurityGroupForm($allUsers, $reservedNames);

		return $form;
	}

	public function succeed(Form $form) {
		$group = new Group();
		$group->setName($form->name);
		foreach ($form->users as $user) {
			$group->addUser($this->getSecurity()->getUser($user));
		}
		$this->getSecurity()->createGroup($group);
		$this->getSecurity()->saveGroups();

		return new Message('Group has been added!');
	}
}

class AdminSecurityPermissionsAction extends AdminBaseAction implements ActionContainer {

	public static function getActions() {
		return array(
		'change' => 'AdminSecurityPermissionsChangeAction'
		);
	}

	public function execute() {
		$this->getDesign()->assign('pages', $this->getModel()->getChilds());
		$this->display('Admin/permissions.tpl');
	}
}

class AdminSecurityPermissionsChangeForm extends Form {

	/**
	 * @var Model
	 */
	private $model;

	/**
	 * @var User
	 */
	private $user;

	public $confirmed = 'false';

	public $who = array();

	public $users = array ();
	private $allUsers = array ();

	public function getAllUsers() {
		return $this->allUsers;
	}

	public $groups = array();
	private $allGroups = array();

	public function getAllGroups() {
		return $this->allGroups;
	}

	private $permissions = array();

	public function getPermissions() {
		return $this->permissions;
	}

	public function __construct(Model $model, User $user, $allUsers, $allGroups, $permissions) {
		$this->model = $model;
		$this->user = $user;
		$this->allUsers = $allUsers;
		$this->allGroups = $allGroups;
		$this->permissions = $permissions;
	}

	public function validate() {
		if($this->model instanceof Site) {
			$found = false;

			foreach($this->model->getPermissions() as $permission) {
				if(Value::get($this->who[$permission->getName()], 'everyone') == 'everyone') {
					$found = true;
				} else {
					foreach(Value::get($this->users[$permission->getName()], array()) as $id) {
						if($id == $this->user->getId()) {
							$found = true;
							break;
						}
					}

					if(!$found) {
						foreach(Value::get($this->groups[$permission->getName()], array()) as $id) {
							if($this->model->getSecurity()->getGroup($id)->hasUser($this->user)) {
								$found = true;
								break;
							}
						}
					}
				}
			}
				
			if(!$found and $this->confirmed != 'true') {
				$this->addError(new WarningMessage('Your about to remove yourself rights from the site. Click again "Save changes" to remove the rights.'));
				$this->confirmed = 'true';
			} else {
				$this->confirmed = 'false';
			}
		}
	}
}

class AdminSecurityPermissionsChangeAction extends AdminBaseFormAction {
	protected function getTemplate() {
		return 'Admin/permission.tpl';
	}

	protected function getReturn() {
		return '/admin/security/permissions';
	}

	private $page;

	public function load() {
		$this->page = $this->getModel()->getPage($this->getRequest()->getParameter(1));
		$this->getDesign()->assign('title', $this->page->getTitle());
	}

	protected function createForm() {
		$allUsers = array ();
		foreach ($this->getSecurity()->getUsers() as $user) {
			$allUsers[$user->getId()] = $user->getNick();
		}

		$allGroups = array ();
		foreach ($this->getSecurity()->getGroups() as $group) {
			$allGroups[$group->getId()] = $group->getName();
		}

		$permissions = array();
		foreach ($this->page->getPermissions() as $permission) {
			$permissions[] = $permission->getName();
		}

		$form = new AdminSecurityPermissionsChangeForm($this->getModel(), $this->getRequest()->getUser(), $allUsers, $allGroups, $permissions);

		return $form;
	}

	private function loadUsers() {

	}

	protected function loadForm(Form $form) {
		$users = array ();
		$groups = array ();

		foreach($this->page->getPermissions() as $permission) {
			if($permission->getEveryone()) {
				$form->who[$permission->getName()] = 'everyone';
			} else {
				$form->who[$permission->getName()] = 'users';
			}

			foreach($permission->getUsers() as $user) {
				$users[$permission->getName()][] = $user->getId();
			}

			foreach($permission->getGroups() as $group) {
				$groups[$permission->getName()][] = $group->getId();
			}
		}

		$form->users = $users;
		$form->groups = $groups;
	}


	protected function succeed(Form $form) {
		$wait = true;

		foreach($this->page->getPermissions() as $permission) {
			$permission->clear();

			foreach(Value::get($form->users[$permission->getName()], array()) as $id) {
				$permission->addUser($this->getSecurity()->getUser($id));
			}

			foreach(Value::get($form->groups[$permission->getName()], array()) as $id) {
				$permission->addGroup($this->getSecurity()->getGroup($id));
			}

			if(Value::get($form->who[$permission->getName()], 'everyone') == 'everyone') {
				$permission->setEveryone(true);
			} else {
				$permission->setEveryone(false);
			}
		}

		$this->getSecurity()->save();
	}
}

?>