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

class Admin extends Extension implements ActionContainer {

	private $editLink;

	public function load() {
		$this->editLink = $this->getProcessor()->findLink($this->getProcessor(), '/admin/pages/edit');
		$this->createIndex($this->getSite());
	}

	private function createIndex(Model $model) {
		foreach ($model->getChilds() as $name => $module) {
			$actionClass = get_class($module) . 'AdminAction';
			if (class_exists($actionClass)) {
				$class = new ReflectionClass($actionClass);
				$adminLink = new SecureLink($module, $module->getTitle(), '/admin/pages/edit/' . $module->getId(), $module->getHide(), $actionClass);
				Processor :: parseAction($module, $adminLink);
			} else {
				$adminLink = new SecureLink($module, $module->getTitle(), '/admin/pages/edit/' . $module->getId(), true, 'DefaultAdminAction');
				$adminLink->addRequiredPermission('edit');
				$adminLink->setSecurity('high');
			}

			$this->editLink->add($module->getId(), $adminLink);
			$this->createIndex($module);
		}
	}

	public static function getActions() {
		return array (
			'admin' => 'AdminAction'
		);
	}
}

abstract class AdminBaseAction extends Action implements ProtectedAction, SecureAction {
	
	public static function getRequiredPermissions() {
		return array('admin');
	}
}

abstract class AdminBaseFormAction extends FormAction implements ProtectedAction, SecureAction {
	
	public static function getRequiredPermissions() {
		return array('admin');
	}
}

class AdminAction extends AdminBaseAction implements ActionContainer {

	public static function getActions() {
		return array (
			'overview' => 'AdminOverviewAction',
			'pages' => 'AdminPagesAction',
			'designs' => 'AdminDesignsAction',
			'security' => 'AdminSecurityAction',
			'settings' => 'AdminSettingsAction'
		);
	}

	public function execute() {
		$this->forward('/admin/overview');
	}
}

class AdminOverviewAction extends AdminBaseAction {
	public function execute() {
		$this->getDesign()->assign('php', phpversion());
		$this->getDesign()->assign('server', $_SERVER['SERVER_SOFTWARE']);
		$this->getDesign()->display('Admin/overview.tpl');
	}
}

?>