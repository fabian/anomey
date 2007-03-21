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

class AdminDesignsAction extends AdminBaseAction implements ActionContainer {

	public static function getActions() {
		return array(
			URI::CHARS . '*' => 'AdminDesignsDesignAction'
		);
	}

	public function execute() {
		$this->getDesign()->assign('designs', $this->getModel()->getDesigns());
		$this->display('Admin/designs.tpl');
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

class AdminDesignsFilesAction extends AdminBaseAction implements ActionContainer {

	public static function getActions() {
		return array(
			URI::CHARS . '*' => 'AdminDesignsFileAction'
		);
	}

	private $design;
	
	protected function load() {
		try {
			$this->design = $this->getModel()->getDesigns($this->getRequest()->getPart(2));
		} catch(DesignNotFoundException $e) {
			$this->forward('/admin/designs', new ErrorMessage('Design does not exist!'));
		}
	}
	
	private function scan($path, $add = '') {
		$files = array();
		
		foreach (scandir($path, 1) as $name) {
			if(substr($name, 0, 1) != '.') {
				if(is_file($path . '/'. $name)) {
					$files[] = array('path' => $add . $name, 'encoded' => URI::encode($add . $name), 'modified' =>  filemtime($path . '/' . $name));
				} elseif (is_dir($path . '/' . $name)) {
					$files = array_merge($files, $this->scan($path . '/'. $name, $name . '/'));
				}
			}
		}
		
		return $files;
	}
	
	/**
	 * @override
	 */
	protected function getBase() {
		return '/admin/designs/' . $this->design->getName();
	}
	
	public function execute() {
		$path = $this->getSecurity()->getProfile() . '/designs/' . $this->design->getName() . '/templates';
		$files = $this->scan($path);

		$this->getDesign()->assign('design', $this->design);
		$this->getDesign()->assign('files', $files);
		$this->display('Admin/design.tpl');
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
				$this->assertNotInList($this->name, $this->designs, new ErrorMessage('There is already a design with this name.'));
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
		// TODO save design settings
		if($form->name != $form->getOrig()) {
			if(rename($this->getSecurity()->getProfile() . '/designs/' . $form->getOrig(), $this->getSecurity()->getProfile() . '/designs/' . $form->getOrig())) {
			} else {
				//error
			}
		}

		return new Message('Changes saved!');
	}
}

?>