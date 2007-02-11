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
		$this->getDesign()->display('Admin/designs.tpl');
	}
}

class AdminDesignsDesignAction extends AdminBaseAction implements ActionContainer {

	public static function getActions() {
		return array(
			'files' => 'AdminDesignsFilesAction'
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
					$files[] = array('path' => $add . $name, 'modified' =>  filemtime($path . '/' . $name));
				} elseif (is_dir($path . '/' . $name)) {
					$files = array_merge($files, $this->scan($path . '/'. $name, $name . '/'));
				}
			}
		}
		
		return $files;
	}
	
	public function execute() {
		$path = $this->getSecurity()->getProfile() . '/designs/' . $this->design->getName() . '/templates';
		$files = $this->scan($path);

		$this->getDesign()->assign('title', $this->design->getTitle());
		$this->getDesign()->assign('files', $files);
		$this->getDesign()->display('Admin/design.tpl');
	}
}

class AdminDesignsFileAction extends AdminBaseAction {

	private $design;
	
	private $file;
	
	protected function load() {
		$this->design = $this->getRequest()->getPart(2);
		$this->file = $this->getRequest()->getPart(4);
	}
	
	protected function getBase() {
		return '/admin/designs';
	}
	
	public function execute() {
		echo 'File!';
		var_dump($this->design);
		var_dump($this->file);
	}
}

?>