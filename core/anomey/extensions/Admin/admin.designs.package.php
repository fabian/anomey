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
		$this->design = $this->getRequest()->getParameter(0);
	}
	
	protected function getBase() {
		return '/admin/designs/' . $this->design;
	}
	
	public function execute() {
		var_dump($this->design);
		$files = array();		

		$path = $this->getSecurity()->getProfile() . '/designs/darkblue/templates';
		foreach (scandir($path) as $name) {
			if(is_file($path . '/'. $name)) {
				$files[] = $name;
			}
		}

		$this->getDesign()->assign('files', $files);
		$this->getDesign()->display('Admin/design.tpl');
	}
}

class AdminDesignsFileAction extends DynamicAction {

	private $design;
	
	protected function load() {
		$this->design = $this->getRequest()->getParameter(0);
	}
	
	protected function getBase() {
		return '/admin/designs/' . $this->design;
	}
	
	protected function index() {
		echo 'File!';
		var_dump($this->design);
	}
}

?>