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

class Admin extends Extension implements ActionContainer {

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
		$this->display('Admin/overview.tpl');
	}
}

?>