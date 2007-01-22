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

 /*
  * @author fabian
  */
 class Join extends Module {

	public function getAvailablePermissions() {
		return array (
			'open' => 'open join',
			'edit' => 'edit join'
		);
	}

	private $url = 'http://anomey.ch/';
	
	public function setUrl($url) {
		$this->url = $url;
		$this->getModel()->setChangedNow();
	}

	public function getUrl() {
		return $this->url;
	}

	public function load() {
		try {
			$this->url = $this->getXml()->url;
		} catch (FileNotFoundException $e) {
			
		}
	}

	public function save() {
		$xml = XML :: create('join');
		$child = $xml->addChild('url', $this->getUrl());

		$this->store($xml);
	}
}

class JoinAction extends Action implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'open'
		);
	}
	
	public function execute() {
		$url = $this->getModel()->getUrl();
		$this->getProcessor()->redirect($this->getRequest(), $url);
	}
}

class JoinAdminForm extends AbstractAdminForm {
	public $url;
	// TODO add support for internal links
	
	public function validate() {
		parent::validate();
		$this->assertNotEmpty($this->url, new ErrorMessage('Please type in an URL!'));
	}
}

class JoinAdminAction extends AbstractDefaultAdminFormAction implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function getTemplate() {
		return 'Admin/Join/url.tpl';
	}

	protected function createAdminForm() {
		return new JoinAdminForm();
	}
	
	protected function loadAdminForm(Form $form) {
		$form->url = $this->getModel()->getUrl();
	}

	public function save(Form $form) {
		$this->getModel()->setUrl($form->url);
		$this->getModel()->save();
		
		return new Message('Changes on join saved!');
	}
}

?>
