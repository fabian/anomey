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

require_once 'form.package.php';
require_once 'action.package.php';
require_once 'store.package.php';

class UserNotFoundException extends Exception {

}

class GroupNotFoundException extends Exception {

}

class WrongTokenException extends Exception {

}

class PermissionNotFoundException extends Exception {

}

class Permission extends Bean {
	
	private $users = array();
	
	public function getUsers() {
		return $this->users;
	}
	
	public function addUser(User $user) {
		$this->users[$user->getId()] = $user;
	}
	
	private $groups= array();
	
	public function getGroups() {
		return $this->groups;
	}
	
	public function addGroup(Group $group) {
		$this->groups[$group->getId()] = $group;
	}
	
	private $everyone = false;
	
	public function getEveryone() {
		return $this->everyone;
	}
	
	public function setEveryone($everyone) {
		$this->everyone = $everyone;
	}
	
	public function isAllowed($user) {
		if($this->everyone) {
			return true;
		} else {
			if($user != null) {
				if(array_key_exists($user->getId(), $this->users)) {
					return true;
				} else {
					foreach($this->groups as $group) {
						if($group->hasUser($user)) {
							return true;
						}
					}
				}
			}
			return false;
		}
	}
	
	public function clear() {
		$this->users = array();
		$this->groups = array();
		$this->everyone = false;
	}
	
	private $name;
	
	public function getName() {
		return $this->name;
	}
	
	public function __construct($name) {
		$this->name = $name;
	}
	
}

class SecurityObject extends Object {
	
	private $security;
	
	public function getSecurity() {
		return $this->security;
	}
	
	public function setSecurity(Security $security) {
		$this->security = $security;
	}
	
	public function getAvailablePermissions() {
		return array(
			'view' => 'view the content of the object',
			'edit' => 'edit the content of the object'
		);
	}
	
	private $permissions = array();
	
	public function getPermissions() {
		return $this->permissions;
	}
	
	public function addPermission(Permission $permission) {
		$this->permissions[$permission->getName()] = $permission;
	}
	
	public function getPermission($name) {
		if(isset($this->permissions[$name])) {
			return $this->permissions[$name];
		} else {
			return new Permission($name);
		}
	}
	
	public function userHasPermission($user, $name) {
		$permission = $this->getPermission($name);
		if($permission->isAllowed($user)) {
			return true;
		} else {
			return false;
		}
	}

	public function checkPermissions($user, $requiredPermissions) {
		$canAccess = false;

		if (count($requiredPermissions) > 0) {

			foreach ($requiredPermissions as $permission) {
				$id = $this->getId();
				
				if($this->userHasPermission($user, $permission)) {
					$canAccess = true;
					break;
				}
			}
		} else {
			$canAccess = true;
		}

		return $canAccess;
	}
	
	private $userPermissions = array();
	
	public function getUserPermissions() {
		return $this->userPermissions;
	}
	
	public function addUserPermission(User $user, $permission) {
		if(!isset($this->userPermissions[$user->getId()])) {
			$this->userPermissions[$user->getId()] = new UserPermission($user);
		}
		$this->userPermissions[$user->getId()]->addPermission($permission);
		$user->addPermission($this->getId(), $permission);
	}
	
	private $groupPermissions = array();
	
	public function getGroupPermissions() {
		return $this->groupPermissions;
	}
	
	public function addGroupPermission(Group $group, $permission) {
		if(!isset($this->groupPermissions[$group->getId()])) {
			$this->groupPermissions[$group->getId()] = new GroupPermission($group);
		}
		$this->groupPermissions[$group->getId()]->addPermission($permission);
		$group->addPermission($this->getId(), $permission);
	}
}

class Security extends Store {

	private $userFile;
	private $groupFile;
	private $securityFile;
	private $tokenFile;

	private $groups = array ();
	
	public function getGroups() {
		return $this->groups;
	}
	
	private $users = array ();
	
	public function getUsers() {
		return $this->users;
	}
	
	private $nextUserId = 1;
	
	private $nextGroupId = 1;
	
	private function getLogin($nick, $password) {
		$user = null;
		foreach($this->users as $tmpUser) {
			if($tmpUser->getNick() == $nick) {
				$user = $tmpUser;
				break;
			}
		}
		
		if ($user != null) {
			if ($user->getPassword() != User::password($password)) {
				$user = null;
			}
		}
		
		return $user;
	}
	
	/**
	 * This method returns
	 */
	public function checkLogin($nick, $password) {		
		if($this->getLogin($nick, $password) != null) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * This method returns
	 */
	public function login($nick, $password) {
		$user = $this->getLogin($nick, $password);
		if ($user != null) {
			return $user;
		} else {
			throw new LoginFailedException();
		}
	}

	public function token($id, $token) {
		try {
			if ($result = XML :: load($this->tokenFile)->xpath("/tokens/token[@user='" . $id . "' and number(concat(substring(@date, 1, 4), substring(@date, 6, 2), substring(@date, 9, 2))) > " . date('Ymd', strtotime('-1 month')) . "]")) {
				$tokens = array ();
				foreach ($result as $token) {
					$tokens[] = (string) $token;
				}
	
				if (in_array($token, $tokens)) {
					return $this->getUser($id);
				} else {
					throw new WrongTokenException();
				}
			} else {
				throw new WrongTokenException('User with id ' . $id . ' has no tokens.');
			}
		} catch (FileNotFoundException $e) {
			
		}
	}

	public function addToken(User $user) {
		$token = sha1(rand() * time());
		$xml = XML :: load($this->tokenFile);
		$element = $xml->addChild('token', $token);
		$element->addAttribute('user', $user->getId());
		$element->addAttribute('date', date('Y-m-d'));
		$xml->save($this->tokenFile);

		return $token;
	}

	public function updateToken(User $user, $token) {
		$xml = XML :: load($this->tokenFile);
		if ($result = $xml->xpath("/tokens/token[@user='" . $user->getId() . "' and text() = '" . $token . "']")) {
			$result[0]['date'] = date('Y-m-d');
			$xml->save($this->tokenFile);
		}
	}

	public function removeToken(User $user, $token) {
		$tokens = array ();
		if ($result = XML :: load($this->tokenFile)->xpath("/tokens/token[not(@user='" . $user->getId() . "' and text() = '" . $token . "')]")) {
			foreach ($result as $token) {
				$tokens[] = $token;
			}
		}
		
		$this->saveTokens($tokens);
	}

	public function clearTokens() {
		$tokens = array ();
		try {
			if ($result = XML :: load($this->tokenFile)->xpath("/tokens/token[number(concat(substring(@date, 1, 4), substring(@date, 6, 2), substring(@date, 9, 2))) > " . date('Ymd', strtotime('-1 month')) . "]")) {
				foreach ($result as $token) {
					$tokens[] = $token;
				}
			}
		} catch (FileNotFoundException $e) {
			
		}
		$this->saveTokens($tokens);
	}

	private function saveTokens($tokens) {
		$xml = XML :: create('tokens');
		foreach ($tokens as $token) {
			$element = $xml->addChild('token', (string) $token);
			$element->addAttribute('user', (string) $token['user']);
			$element->addAttribute('date', (string) $token['date']);
		}
		$xml->save($this->tokenFile);
	}
	
	const userFile = 'xml/users.xml';
	const groupFile = 'xml/groups.xml';
	const securityFile = 'xml/security.xml';
	const tokenFile = 'xml/token.xml';
	
	private $objects = array();

	public function __construct($profile) {
		$this->securityFile = $profile . '/' . self :: securityFile;
		
		parent::__construct($profile, $this->securityFile);
		
		$this->userFile = $this->getProfile() . '/' . self :: userFile;
		$this->groupFile = $this->getProfile() . '/' . self :: groupFile;
		$this->tokenFile = $this->getProfile() . '/' . self :: tokenFile;
		
		try {
			$userXML = XML :: load($this->userFile);		
			$this->nextUserId = (int) $userXML['nextid'];
	
			// load users
			foreach ($userXML->user as $userElement) {
				$user = new User();
				$user->setId((string) $userElement['id']);
				$user->setNick((string) $userElement['nick']);
				$user->setPassword((string) $userElement['password']);
				$user->setPrename((string) $userElement['prename']);
				$user->setLastname((string) $userElement['lastname']);
				$user->setTitle((string) $userElement['title']);
				$user->setUrl((string) $userElement['url']);
				$user->setMail((string) $userElement['mail']);
				
				$this->users[$user->getId()] = $user;
			}
		} catch (FileNotFoundException $e) {
			$root = new User();
			$root->setId('u0');
			$root->setNick('root');
			$root->setPassword(sha1('root'));
			$this->users['u0'] = $root;
		}
		
		try {
			$groupXML = XML :: load($this->groupFile);		
			$this->nextGroupId = (int) $groupXML['nextid'];
	
			// load groups
			foreach ($groupXML->group as $groupElement) {
				$group = new Group();
				$group->setId((string) $groupElement['id']);
				$group->setName((string) $groupElement['name']);
				
				$this->groups[$group->getId()] = $group;
				
				foreach($groupElement->user as $userElement) {
					if(isset($this->users[(string) $userElement])) {
						$user = $this->users[(string) $userElement];
						$group->addUser($user);
						$user->addGroup($group);
					}
				}
			}
		} catch (FileNotFoundException $e) {
			
		}

		try {
			$securityXML = XML :: load($this->securityFile);
	
			// load permissions
			foreach ($this->getObjects() as $id => $object) {
				$object->setSecurity($this);
				foreach($object->getAvailablePermissions() as $name => $description) {
					$permission = new Permission($name);				
					if($permissionElement = $securityXML->xpath("//object[@id='".$id."']/permission[@name='".$name."']")) {
						foreach($permissionElement[0]->user as $userElement) {
							if(isset($this->users[(string) $userElement['id']])) {
								$user = $this->users[(string) $userElement['id']];
								$permission->addUser($user);
							}
						}
						
						foreach($permissionElement[0]->group as $groupElement) {
							if(isset($this->groups[(string) $groupElement['id']])) {
								$group = $this->groups[(string) $groupElement['id']];
								$permission->addGroup($group);
							}
						}
						
						if($permissionElement[0]['everyone'] == 'true') {
							$permission->setEveryone(true);
						}
					}
					$object->addPermission($permission);
				}
			}
		} catch (FileNotFoundException $e) {
			$permission = new Permission('admin');
			$permission->addUser($this->users['u0']);
			$this->getObject(Site::ID)->addPermission($permission);
		}
	}
	
	public function getUser($id) {
		if(isset($this->users[$id])) {
			return $this->users[$id];
		} else {
			// no such user
			throw new UserNotFoundException('User with id ' . $id . ' could no be found!');
		}
	}
	
	public function createUser(User $user) {
		$user->setId($id = 'u'.$this->nextUserId++);
		$this->users[$user->getId()] = $user;
	}
	
	public function removeUser($id) {
		if(isset($this->users[$id])) {
			unset($this->users[$id]);
		} else {
			// no such user
			throw new UserNotFoundException('User with id ' . $id . ' could no be found!');
		}
	}
	
	public function saveUsers() {
		$xml = XML :: create('users');
		$xml->addAttribute('nextid', $this->nextUserId);
		foreach ($this->users as $user) {
			$element = $xml->addChild('user');
			$element->addAttribute('id', $user->getId());
			$element->addAttribute('nick', $user->getNick());
			if($user->getPassword() != '') $element->addAttribute('password', $user->getPassword());
			if($user->getPrename() != '') $element->addAttribute('prename', $user->getPrename());
			if($user->getLastname() != '') $element->addAttribute('lastname', $user->getLastname());
			if($user->getTitle() != '') $element->addAttribute('title', $user->getTitle());
			if($user->getUrl() != '') $element->addAttribute('url', $user->getUrl());
			if($user->getMail() != '') $element->addAttribute('mail', $user->getMail());
		}
		$xml->save($this->userFile);
	}
	
	public function createGroup(Group $group) {
		$group->setId($id = 'g'.$this->nextGroupId++);
		$this->groups[$group->getId()] = $group;
	}
	
	public function removeGroup($id) {
		if(isset($this->groups[$id])) {
			unset($this->groups[$id]);
		} else {
			// no such group
			throw new GroupNotFoundException('Group with id ' . $id . ' could no be found!');
		}
	}
	
	public function saveGroups() {
		$xml = XML :: create('groups');
		$xml->addAttribute('nextid', $this->nextGroupId);
		foreach ($this->groups as $group) {
			$element = $xml->addChild('group');
			$element->addAttribute('id', $group->getId());
			$element->addAttribute('name', $group->getName());
			foreach($group->getUsers() as $user) {
				$userElement = $element->addChild('user', $user->getId());
			}
		}
		$xml->save($this->groupFile);
	}
	
	public function getGroup($id) {
		if(isset($this->groups[$id])) {
			return $this->groups[$id];
		} else {
			// no such group
			throw new GroupNotFoundException();
		}
	}

	public function save() {
		$xml = XML :: create('objects');
		$xml->addAttribute('nextid', $this->getNextId());

		foreach ($this->getObjects() as $object) {
			$objectElement = $xml->addChild('object');
			$objectElement->addAttribute('id', $object->getId());
			$objectElement->addAttribute('class', get_class($object));
			
			foreach($object->getPermissions() as $permission) {
				$permissionElement = $objectElement->addChild('permission');
				$permissionElement->addAttribute('name', $permission->getName());
				if($permission->getEveryone()) {
					$permissionElement->addAttribute('everyone', 'true');
				}
				foreach($permission->getUsers() as $user) {
					$userElement = $permissionElement->addChild('user');
					$userElement->addAttribute('id', $user->getId());
				}
				foreach($permission->getGroups() as $group) {
					$groupElement = $permissionElement->addChild('group');
					$groupElement->addAttribute('id', $group->getId());
				}
			}
		}

		$xml->save($this->securityFile);
	}
}

class LoginForm extends Form {
	public $username = '';
	public $password = '';
	public $forward = '/';
	public $remember = 'false';
	
	private $security;

	public function __construct(Security $security) {
		$this->security = $security;
	}

	public function validate() {
		$un = $this->assertNotEmpty($this->username, new Message('Please type in a username.', 'error'));
		$pw = $this->assertNotEmpty($this->password, new Message('Please type in your password', 'error'));

		if ($un && $pw) {
			$this->assertTrue($this->security->checkLogin($this->username, $this->password), new Message('Username and password did not match!', 'error'));
		}

	}
}

class LoginAction extends FormAction {
	protected function getTemplate() {
		return 'login.tpl';
	}
	
	protected function getReturn() {
		return $this->getForm()->forward;
	}

	public function createForm() {
		$form = new LoginForm($this->getSecurity());
		if ($this->getRequest()->getTrail() != '/login') {
			$form->forward = $this->getRequest()->getTrail();
		}
		return $form;
	}

	protected function succeed(Form $form) {
		$remember = $form->remember == 'true' ? true : false;
		$user = $this->getSecurity()->login($form->username, $form->password);
		$user->setTrusted(true);

		$session = $this->getRequest()->getSession();
		$session->store('systemUser', $user->getId());
		$session->store('systemUserTrusted', $user->getTrusted());
		$session->regenerate();

		if ($remember) {
			// Expires after a month
			$expire = strtotime('+ 1 month');
			$cookie = $this->getRequest()->getCookie();
			$cookie->store('systemUser', $user->getId(), $expire);
			$cookie->store('systemUserToken', $this->getSecurity()->addToken($user), $expire);
		}

		return new Message('You\'re now logged in as ' . $user->getNick());
	}
}

class TrustedForm extends Form {
	private $username;
	private $security;

	public function __construct($username, Security $security) {
		$this->username = $username;
		$this->security = $security;
	}

	public $password = '';
	public $forward = '/';

	public function validate() {
		$pw = $this->assertNotEmpty($this->password, new Message('Please type in your password', 'error'));

		if ($pw) {
			$this->assertTrue($this->security->checkLogin($this->username, $this->password), new Message('Wrong password!', 'error'));
		}

	}
}

class TrustedAction extends FormAction {
	protected function getTemplate() {
		return 'trusted.tpl';
	}
	
	protected function getReturn() {
		return $this->getForm()->forward;
	}

	public function createForm() {
		$form = new TrustedForm($this->getRequest()->getUser()->getNick(), $this->getSecurity());
		if ($this->getRequest()->getTrail() != '/trusted') {
			$form->forward = $this->getRequest()->getTrail();
		}
		return $form;
	}

	protected function succeed(Form $form) {
		$user = $this->getRequest()->getUser();
		$user->setTrusted(true);
		$this->getRequest()->getSession()->store('systemUserTrusted', $user->getTrusted());
	}
}

class LogoutAction extends Action {
	public function execute() {
		$session = $this->getRequest()->getSession();
		$session->clear('systemUser');
		$session->regenerate();

		$cookie = $this->getRequest()->getCookie();
		$this->getSecurity()->removeToken($this->getRequest()->getUser(), $cookie->get('systemUserToken'));
		$cookie->clear('systemUser');
		$cookie->clear('systemUserToken');

		$this->forward('/', new Message('You\'re now logged out.'));
	}
}

class AccessDeniedException extends Exception {
}

class TrustedUserRequiredException extends Exception {
}

class User extends Bean {

	private $id = '';

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	private $nick = '';

	public function getNick() {
		return $this->nick;
	}

	public function setNick($nick) {
		$this->nick = $nick;
	}
	
	/**
	 * SHA1 encrypted password
	 */
	private $password = '';
	
	public function getPassword() {
		return $this->password;
	}
	
	public function setPassword($password) {
		$this->password = $password;
	}
	
	/**
	 * This method generates the password hash
	 * for the given password.
	 */
	public static function password($password) {
		return sha1($password);
	}

	private $prename = '';

	public function getPrename() {
		return $this->prename;
	}

	public function setPrename($prename) {
		$this->prename = $prename;
	}

	private $lastname = '';

	public function getLastname() {
		return $this->lastname;
	}

	public function setLastname($lastname) {
		$this->lastname = $lastname;
	}

	public function getFullname() {
		return $this->getPrename() . ' ' . $this->getLastname();
	}
	
	public function getName() {
		if($this->getPrename() == '' && $this->getLastname() == '') {
			return $this->getNick();
		} else {
			return $this->getFullname();
		}
	}

	private $mail = '';

	public function getMail() {
		return $this->mail;
	}

	public function setMail($mail) {
		$this->mail = $mail;
	}
	
	private $title = '';
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}
	
	private $url = '';
	
	public function getUrl() {
		return $this->url;
	}
	
	public function setUrl($url) {
		$this->url = $url;
	}

	private $groups = array ();

	public function getGroups() {
		return $this->groups;
	}

	public function setGroups($groups) {
		$this->groups = $groups;
	}

	public function addGroup(Group $group) {
		$this->groups[] = $group;
	}

	private $trusted = false;

	public function setTrusted($trusted) {
		$this->trusted = $trusted;
	}

	public function getTrusted() {
		return $this->trusted;
	}

	public function __construct() {

	}
}

class Group extends Bean {
	private $id = '';

	public function getId() {
		return $this->id;
	}

	public function setId($id) {
		$this->id = $id;
	}

	private $name = '';

	public function getName() {
		return $this->name;
	}

	public function setName($name) {
		$this->name = $name;
	}
	
	private $users = array();
	
	public function getUsers() {
		return $this->users;
	}
	
	public function hasUser($user) {
		if(array_key_exists($user->getId(), $this->users)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function addUser(User $user) {
		if(!isset($this->users[$user->getId()])) {
			$this->users[$user->getId()] = $user;
		}
	}
	
	public function clearUsers() {
		$this->users = array();
	}

	public function __construct() {
	}
	
	private $permissions = array();
	
	public function addPermission($oid, $permission) {
		if(isset($this->permissions[$oid])) {
			$this->permissions[$oid][] = $permission;
		} else {
			$this->permissions[$oid] = array($permission);
		}
	}

	public function getPermissions($oid) {
		return Value :: get($this->permissions[$oid], array ());
	}
}

?>
