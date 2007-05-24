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

class AdminDesignsForm extends Form {

	public $toDelete = array();

	public function validate() {
		$this->assertTrue(count($this->toDelete) > 0, new ErrorMessage('Please select at least one design to delete.'));
	}
}

class AdminDesignsAction extends AdminBaseFormAction implements ActionContainer {

	public static function getActions() {
		return array(
		'add' => 'AdminDesignsAddAction',
		URI::CHARS . '*' => 'AdminDesignsDesignAction'
		);
	}

	public function getTemplate() {
		return 'Admin/designs.tpl';
	}

	protected function load() {
		$this->getDesign()->assign('designs', $this->getModel()->getDesigns());
	}

	public function createForm() {
		return new AdminDesignsForm();
	}

	public function succeed(Form $form) {
		$error = false;

		foreach($form->toDelete as $design) {
			if(!FileSytem::rmdir($this->getSecurity()->getProfile() . '/designs/' . $design)) {
				$error = true;
			}
		}

		if($error) {
			return new ErrorMessage('Not all selected designs could have been deleted.');
		} else {
			return new Message('Selected design(s) deleted.');
		}
	}
}

class AdminDesignsAddForm extends Form {

	public $title = '';

	public $name = '';

	public $author = '';

	public $license = '';
	
	private $designs;
	
	public function __construct($designs) {
		$this->designs = $designs;
	}

	public function validate() {
		$this->assertNotEmpty($this->title, new ErrorMessage('Please type in a title.'));
		if($this->assertNotEmpty($this->name, new ErrorMessage('Please type in a name.'))) {
			if($this->assertRegEx($this->name, '/^' . FileSytem::CHARS . '*$/', new ErrorMessage('The name may consists only of letters, digits, hyphens and underlines.'))) {
				$this->assertNotInList($this->name, $this->designs, new ErrorMessage('The name is already in use. Please type in another name.'));
			}
		}
	}
}

class AdminDesignsAddAction extends AdminBaseFormAction {


	public function getTemplate() {
		return 'Admin/designAdd.tpl';
	}

	protected function createForm() {
		return new AdminDesignsAddForm(array_keys($this->getModel()->getDesigns()));
	}

	protected function getReturn() {
		return '/admin/designs';
	}

	protected function loadForm(Form $form) {
	}

	public function succeed(Form $form) {
		$designPath = $this->getSecurity()->getProfile() . '/designs/' . $form->name;

		@mkdir($designPath . '/templates', 0755, true);

		$xml = XML :: create('design');
		$xml->addChild('title', $form->title);
		$authorElement = $xml->addChild('author');
		$authorElement->addChild('name', $form->author);
		$xml->addChild('license', $form->license);
		$xml->save($designPath . '/design.xml');
		
		return new Message('Design created!');
	}
}

class AdminDesignsDesignAction extends AdminBaseAction implements ActionContainer {

	public static function getActions() {
		return array(
		'files' => 'AdminDesignsFilesAction',
		'settings' => 'AdminDesignsSettingsAction'
		);
	}

	private $design;

	protected function load() {
		$this->design = $this->getRequest()->getParameter(0);
	}

	protected function getBase() {
		return '/admin/designs/' . $this->design;
	}

	public function execute() {
		$this->forward('files');
	}
}

class AdminDesignsFilesForm extends Form {

	public $toDelete = array();

	public function validate() {
		$this->assertTrue(count($this->toDelete) > 0, new ErrorMessage('Please select at least one file to delete.'));
	}
}

class AdminDesignsFilesAction extends AdminBaseFormAction implements ActionContainer {

	public static function getActions() {
		return array(
		'add' => 'AdminDesignsCreateFileAction',
		'copy' => 'AdminDesignsCopyFileAction',
		URI::CHARS . '*' => 'AdminDesignsFileAction'
		);
	}

	public function getTemplate() {
		return 'Admin/design.tpl';
	}

	private $design;

	protected function load() {
		try {
			$this->design = $this->getModel()->getDesigns($this->getRequest()->getPart(2));
				
			$path = $this->getSecurity()->getProfile() . '/designs/' . $this->design->getName() . '/templates';
			$files = $this->scan($path);

			$this->getDesign()->assign('design', $this->design);
			$this->getDesign()->assign('files', $files);
		} catch(DesignNotFoundException $e) {
			$this->forward('/admin/designs', new ErrorMessage('Design does not exist!'));
		}
	}

	private function scan($path, $add = '') {
		$files = array();

		foreach (scandir($path) as $name) {
			if(substr($name, 0, 1) != '.') {
				if(is_file($path . '/'. $name)) {
					$files[] = array('path' => $add . $name, 'encoded' => URI::encode($add . $name), 'modified' =>  filemtime($path . '/' . $name));
				} elseif (is_dir($path . '/' . $name)) {
					$files = array_merge($files, $this->scan($path . '/'. $name, $add . $name . '/'));
				}
			}
		}

		return $files;
	}

	public function createForm() {
		return new AdminDesignsFilesForm();
	}

	/**
	 * @override
	 */
	protected function getBase() {
		return '/admin/designs/' . $this->design->getName();
	}

	public function succeed(Form $form) {
		$error = false;

		foreach($form->toDelete as $file) {
			if(!@unlink($this->getSecurity()->getProfile() . '/designs/' . $this->design->getName() . '/templates/' . URI::decode($file))) {
				$error = true;
			}
		}

		if($error) {
			return new ErrorMessage('Not all selected files could have been deleted.');
		} else {
			return new Message('Selected file(s) deleted.');
		}
	}
}

class AdminDesignsCreateFileForm extends Form {

	public $file;

	public $contentOfFile;

	public $confirmed = 'false';

	private $profile;

	private $design;

	public function getPath() {
		return $this->profile . '/designs/' . $this->design . '/templates/' . $this->file;
	}

	public function __construct($profile, $design) {
		$this->profile = $profile;
		$this->design = $design;
	}

	public function validate() {
		if(file_exists($this->getPath())) {
			$this->assertTrue(is_writable($this->getPath()), new ErrorMessage('You don\'t have the permission to create this file.'));
		}

		if(file_exists($this->getPath()) and $this->confirmed != 'true') {
			$this->addError(new WarningMessage('There is already a file with this filename. Click again "Save changes" to override the file.'));
			$this->confirmed = 'true';
		} else {
			$this->confirmed = 'false';
		}
	}
}

class AdminDesignsCreateFileAction extends AdminBaseFormAction {

	private $design;

	private $file;

	protected function load() {
		try {
			$this->design = $this->getModel()->getDesigns($this->getRequest()->getPart(2));
		} catch(DesignNotFoundException $e) {
			$this->forward('/admin/designs', new ErrorMessage('Design does not exist!'));
		}
		$this->getDesign()->assign('design', $this->design);
	}


	public function getTemplate() {
		return 'Admin/designNewFile.tpl';
	}

	protected function createForm() {
		return new AdminDesignsCreateFileForm($this->getSecurity()->getProfile(), $this->design->getName());
	}

	/**
	 * @override
	 */
	protected function getBase() {
		return '/admin/designs/' . $this->design->getName();
	}

	protected function getReturn() {
		return 'files';
	}

	protected function loadForm(Form $form) {
		if(!is_writable($form->getPath())) {
			$this->getRequest()->addMessage(new WarningMessage('You don\'t have the permission to edit this file.'));
		}
	}

	public function succeed(Form $form) {
		$dir = dirname($form->getPath());
		
		if(!@file_exists($dir)) {
			mkdir($dir, 0755, true);
		}
		
		if(!file_put_contents($form->getPath(), HTML::decodeSpecialchars($form->contentOfFile))) {
			return new ErrorMessage('Could not save file!');
		} else {
			return new Message('Changes saved!');
		}
	}
}

class AdminDesignsCopyFileForm extends Form {

	public $filesToCopy = array();

	private $files;

	public function __construct($files) {
		$this->files = $files;
	}

	public function getFiles() {
		return $this->files;
	}

	public function validate() {
	}
}

class AdminDesignsCopyFileAction extends AdminBaseFormAction {

	private $design;

	protected function load() {
		try {
			$this->design = $this->getModel()->getDesigns($this->getRequest()->getPart(2));
		} catch(DesignNotFoundException $e) {
			$this->forward('/admin/designs', new ErrorMessage('Design does not exist!'));
		}
		$this->getDesign()->assign('design', $this->design);
	}


	public function getTemplate() {
		return 'Admin/designCopyFile.tpl';
	}

	protected function createForm() {
		$path = 'core/anomey/extensions/Admin/templates';
		$files = $this->scan($path);
		return new AdminDesignsCopyFileForm($files);
	}

	/**
	 * @override
	 */
	protected function getBase() {
		return '/admin/designs/' . $this->design->getName();
	}

	protected function getReturn() {
		return 'files';
	}

	protected function loadForm(Form $form) {
	}

	private function scan($path, $add = '') {
		$files = array();

		foreach (scandir($path) as $name) {
			if(substr($name, 0, 1) != '.') {
				if(is_file($path . '/'. $name)) {
					$files[URI::encode($path . '/'. $name . ':' .  $add . $name)] = $add . $name;
				} elseif (is_dir($path . '/' . $name)) {
					$files = $files + $this->scan($path . '/'. $name, $add . $name . '/');
				}
			}
		}

		return $files;
	}

	public function succeed(Form $form) {
		$error = false;

		foreach ($form->filesToCopy as $file) {
			list($path, $name) = split(':', URI::decode($file), 2);
			
			$target = $this->getSecurity()->getProfile() . '/designs/' . $this->design->getName() . '/templates/' . $name;
				
			$dir = dirname($target);
			
			if(!@file_exists($dir)) {
				mkdir($dir, 0755, true);
			}
		
			if (!@copy($path, $target)) {
				$error = true;
			}
		}

		if($error) {
			return new WarningMessage('Not all file(s) could have been copied!');
		} else {
			return new Message('File(s) copied!');
		}
	}
}

class AdminDesignsFileForm extends Form {

	public $file;

	public $contentOfFile;

	public $confirmed = 'false';

	private $profile;

	private $design;

	private $orig;

	public function getPath() {
		return $this->profile . '/designs/' . $this->design . '/templates/' . $this->file;
	}

	public function getOrig() {
		return $this->orig;
	}

	public function getOrigPath() {
		return $this->profile . '/designs/' . $this->design . '/templates/' . $this->orig;
	}

	public function __construct($profile, $design, $orig) {
		$this->profile = $profile;
		$this->design = $design;
		$this->orig = $orig;
	}

	public function validate() {
		if(file_exists($this->getPath())) {
			$this->assertTrue(is_writable($this->getPath()), new ErrorMessage('You don\'t have the permission to edit this file.'));
		}

		if($this->file != $this->orig and file_exists($this->getPath()) and $this->confirmed != 'true') {
			$this->addError(new WarningMessage('There is already a file with this filename. Click again "Save changes" to override the file.'));
			$this->confirmed = 'true';
		} else {
			$this->confirmed = 'false';
		}
	}
}

class AdminDesignsFileAction extends AdminBaseFormAction {

	private $design;

	private $file;

	protected function load() {
		try {
			$this->design = $this->getModel()->getDesigns($this->getRequest()->getPart(2));
		} catch(DesignNotFoundException $e) {
			$this->forward('/admin/designs', new ErrorMessage('Design does not exist!'));
		}
		$this->getDesign()->assign('design', $this->design);
		$this->file = URI::decode($this->getRequest()->getPart(4));
	}


	public function getTemplate() {
		return 'Admin/designFile.tpl';
	}

	protected function createForm() {
		return new AdminDesignsFileForm($this->getSecurity()->getProfile(), $this->design->getName(), $this->file);
	}

	/**
	 * @override
	 */
	protected function getBase() {
		return '/admin/designs/' . $this->design->getName();
	}

	protected function getReturn() {
		return 'files';
	}

	protected function loadForm(Form $form) {
		$form->file = $this->file;
		$form->contentOfFile = HTML::specialchars(file_get_contents($form->getPath()));
		if(!is_writable($form->getPath())) {
			$this->getRequest()->addMessage(new WarningMessage('You don\'t have the permission to edit this file.'));
		}
	}

	public function succeed(Form $form) {
		if(!file_put_contents($form->getPath(), HTML::decodeSpecialchars($form->contentOfFile))) {
			return new ErrorMessage('Could not save file!');
		} else {
			if($form->file != $form->getOrig()) {
				// remove old file
				unlink($form->getOrigPath());
			}
			return new Message('Changes saved!');
		}
	}
}

class AdminDesignsSettingsForm extends Form {

	public $name = '';

	public $title = '';

	public $author = '';

	public $license = '';

	private $orig;

	private $designs = array();

	public function getOrig() {
		return $this->orig;
	}

	public function getDesigns() {
		return $this->designs;
	}

	public function __construct($orig, $designs) {
		$this->orig = $orig;
		$this->designs = $designs;
	}

	public function validate() {
		if($this->assertNotEmpty($this->name, new ErrorMessage('Please type in a name.'))) {
			if($this->name != $this->getOrig()) {
				if($this->assertRegEx($this->name, '/^' . FileSytem::CHARS . '*$/', new ErrorMessage('The name may consists only of letters, digits, hyphens and underlines.'))) {
					$this->assertNotInList($this->name, $this->designs, new ErrorMessage('There is already a design with this name.'));
				}
			}
		}
		$this->assertNotEmpty($this->title, new ErrorMessage('Please type in a title.'));
	}
}

class AdminDesignsSettingsAction extends AdminBaseFormAction {

	private $design;

	protected function load() {
		try {
			$this->design = $this->getModel()->getDesigns($this->getRequest()->getPart(2));
		} catch(DesignNotFoundException $e) {
			$this->forward('/admin/designs', new ErrorMessage('Design does not exist!'));
		}
		$this->getDesign()->assign('design', $this->design);
	}

	public function getTemplate() {
		return 'Admin/designSettings.tpl';
	}

	protected function getReturn() {
		return 'settings';
	}

	protected function createForm() {
		return new AdminDesignsSettingsForm($this->design->getName(), array());
	}

	/**
	 * @override
	 */
	protected function getBase() {
		return '/admin/designs/' . $this->design->getName();
	}

	protected function loadForm(Form $form) {
		$form->title = $this->design->getTitle();
		$form->name = $this->design->getName();
		$form->author = $this->design->getAuthor();
		$form->license = $this->design->getLicense();
	}

	public function succeed(Form $form) {
		$this->design->setTitle($form->title);
		$this->design->setAuthor($form->author);
		$this->design->setlicense($form->license);

		$designPath = $this->getSecurity()->getProfile() . '/designs/' . $form->getOrig();

		$xml = XML :: create('design');
		$xml->addChild('title', $form->title);
		$authorElement = $xml->addChild('author');
		$authorElement->addChild('name', $form->author);
		$xml->addChild('license', $form->license);
		$xml->save($designPath . '/design.xml');
		
		if($form->name != $form->getOrig()) {
			if(@rename($designPath, $this->getSecurity()->getProfile() . '/designs/' . $form->name)) {
				$this->design->setName($form->name);
			} else {
				$this->getRequest()->addMessage(new WarningMessage('Could not rename design folder.'));
			}
		}

		return new Message('Changes saved!');
	}
}

?>