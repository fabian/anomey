<?php

/*
 * anomey 2.1 - content management
 * ================================
 * 
 * Copyright © 2006, 2007 - Adrian Egloff <adrian@anomey.ch>, 
 * Cyril Gabathuler <cyril@anomey.ch> and Fabian Vogler <fabian@anomey.ch>
 * 
 * This file is part of anomey. For more information about anomey
 * visit <http://anomey.ch/> or write a mail to <info@anomey.ch>.
 * 
 * anomey is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * anomey is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with anomey (license.txt). If not, see <http://www.gnu.org/licenses/>.
 */

class AdminSettingsForm extends Form {
	public $title = '';
	public $design = '';

	public $home = '';
	public $accessDenied = '';
	public $pageNotFound = '';
	
	private $designs = array();
	
	public function getDesigns() {
		return $this->designs;
	}

	public function __construct($designs) {
		$this->designs[''] = '';
		foreach($designs as $design) {
			$this->designs[$design->getName()] = $design->getTitle(); 
		}
	}

	protected function validate() {
		$this->assertNotEmpty($this->title, new ErrorMessage('Please type in a title.'));
		$this->assertInList($this->design, array_keys($this->designs), new ErrorMessage('Please select a design from the list.'));

		$this->assertNotEmpty($this->home, new ErrorMessage('Please type the path to the home page.'));
		$this->assertNotEmpty($this->accessDenied, new ErrorMessage('Please type the path to the "access denied" page.'));
		$this->assertNotEmpty($this->pageNotFound, new ErrorMessage('Please type the path to the "page not found" page.'));
	}
}

class AdminSettingsAction extends AdminBaseFormAction {
	public function getTemplate() {
		return 'Admin/settings.tpl';
	}

	protected function getReturn() {
		return '/admin/settings';
	}

	protected function createForm() {
		$form = new AdminSettingsForm($this->getModel()->getDesigns());

		return $form;
	}

	protected function loadForm(Form $form) {
		$form->title = $this->getModel()->getTitle();
		$form->design = $this->getModel()->getDesign()->getName();

		$form->home = $this->getProcessor()->getHomeTrail();
		$form->accessDenied = $this->getProcessor()->getAccessDeniedTrail();
		$form->pageNotFound = $this->getProcessor()->getPageNotFoundTrail();
	}

	public function succeed(Form $form) {
		$this->getModel()->setTitle($form->title);
		$designs = $this->getModel()->getDesigns();
		$this->getModel()->setDesign($designs[$form->design]);
		$this->getModel()->save();

		$this->getProcessor()->setHomeTrail($form->home);
		$this->getProcessor()->setAccessDeniedTrail($form->accessDenied);
		$this->getProcessor()->setPageNotFoundTrail($form->pageNotFound);
		$this->getProcessor()->save();

		return new Message('Changes saved!');
	}
}

?>