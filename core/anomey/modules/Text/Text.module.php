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
 * @date 14.02.2006
 * @author fabian
 */
class Text extends Module {

	public function getAvailablePermissions() {
		return array (
			'view' => 'view content of page',
			'edit' => 'change content of page'
		);
	}

	private $content = '';
	
	public function setContent($content) {
		$this->content = $content;
	}

	public function getContent() {
		return $this->content;
	}

	public function load() {
		if(!file_exists($this->getMediaPath())){
			mkdir($this->getMediaPath(), 0755, true);
		}
		try {
			$this->content = $this->getXml()->content;
		} catch (FileNotFoundException $e) {
			
		}
	}

	public function save() {
		$xml = XML :: create('page');
		$child = $xml->addChild('content', $this->getContent());

		$this->store($xml);
	}
}

class TextAction extends Action implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'view'
		);
	}
	
	public function execute() {
		$content = $this->getModel()->getContent();
		$this->getDesign()->assign('content', $content);
		$this->getDesign()->display('Text/content.tpl');
	}
}

class TextAdminAction extends AbstractAdminAction implements ActionContainer {

	public static function getActions() {
		return array (
			'content' => 'TextAdminContentAction',
			'media' => 'TextAdminMediaAction',
			'settings' => 'TextAdminSettingsAction'
		);
	}

	public function execute() {
		$this->forward('content');
	}
}

class TextAdminForm extends Form {
	
	public $contentOfPage = '';
	
	public function validate() {
		
	}
}

class TextAdminContentAction extends AbstractAdminFormAction implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function getTemplate() {
		return 'Admin/Text/content.tpl';
	}

	protected function createForm() {
		return new TextAdminForm();
	}

	protected function loadForm(Form $form) {
		$form->contentOfPage = $this->getModel()->getContent();
	}

	public function succeed(Form $form) {		
		$this->getModel()->setContent($form->contentOfPage);
		$this->getModel()->save();
		
		$this->getModel()->setModifiedNow();
		$this->getModel()->getSite()->save();

		return new Message('Changes saved!');
	}
}

class TextAdminMediaAction extends AbstractAdminAction implements ActionContainer, ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}

	public static function getActions() {
		return array (
			'add' => 'TextAdminAddMediaAction',
			'delete' => 'TextAdminDeleteMediaAction'
		);
	}

	public function execute() {
		$this->getDesign()->assign('files', FileSytem::get($this->getModel()->getMediaPath()));
		$this->getDesign()->display('Admin/Text/media.tpl');
	}
}

class TextAdminMediaForm extends Form {
	
	public $file = '';
	public $name = '';
	
	private $model;
	
	public function __construct(Model $model) {
		$this->model = $model;
	}
	
	public function getTarget() {
		$target = $this->model->getMediaPath() . '/';
		if($this->name == '') {
			$target .= basename($_FILES['file']['name']);
		} else {
			$target .= $this->name;
		}
		return $target;
	}
	
	protected function validate() {
		if($this->assertNotEmpty($_FILES['file']['tmp_name'], new ErrorMessage('Please choose a file.'))) {
			if($this->name != '') {
				$this->assertRegEx($this->name, '/^' . FileSytem::CHARS . '*$/', new ErrorMessage('The name may consists only of letters, digits, hyphens and underlines.'));
			}
			$this->assertFalse(file_exists($this->getTarget()), new ErrorMessage('This file already exists.'));
		}
	}
}

class TextAdminAddMediaAction extends AbstractAdminFormAction {

	public function getTemplate() {
		return 'Admin/Text/add-media.tpl';
	}
	
	protected function getReturn() {
		return 'media';
	}
	
	protected function createForm() {
		return new TextAdminMediaForm($this->getModel());
	}
	
	public function succeed(Form $form) {
		if(move_uploaded_file($_FILES['file']['tmp_name'], $form->getTarget())) {
			return new Message('File uploaded!');
		} else{
			return new WarningMessage('Could not upload file.');
		}

	}
}

class TextAdminDeleteMediaAction extends AbstractAdminAction {
	public function execute() {
		$names = array();
		foreach(FileSytem::get($this->getModel()->getMediaPath()) as $file) {
			$names[] = $file->getName();
		}
		
		$file = $this->getRequest()->getParameter('file');
		if(in_array($file, $names)) {
			unlink($this->getModel()->getMediaPath().'/'.$file);
		}
		$this->forward('media', new Message('File deleted!'));
	}
}

class TextAdminSettingsForm extends AbstractAdminForm {
}

class TextAdminSettingsAction extends AbstractDefaultAdminFormAction implements ProtectedAction {
	
	public static function getRequiredPermissions() {
		return array(
			'edit'
		);
	}
	
	public function getTemplate() {
		return 'Admin/Text/settings.tpl';
	}
	
	protected function getReturn() {
		return 'settings';
	}

	protected function createAdminForm() {
		return new TextAdminSettingsForm();
	}

	public function save(Form $form) {
		$this->getModel()->save();
		
		return new Message('Settings saved!');
	}
}

?>
