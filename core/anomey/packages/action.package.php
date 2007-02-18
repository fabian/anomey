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

interface WebAction {
	public function __construct(Processor $processor, Request $request, Model $model, Security $security);
	public function getContentType();
	public function execute();
}

interface ActionContainer {
	public static function getActions();
}

interface ProtectedAction {
	public static function getRequiredPermissions();
}

interface SecureAction {
}

/**
 * Action class.
 */
abstract class Action implements WebAction {
	
	private $processor;
	
	protected function getProcessor() {
		return $this->processor;
	}
	
	private $request;

	protected function getRequest() {
		return $this->request;
	}
	
	private $model;

	protected function getModel() {
		return $this->model;
	}
	
	private $security;
	
	protected function getSecurity() {
		return $this->security;
	}

	private $design;

	protected function getDesign() {
		return $this->design;
	}
	
	protected function getBase() {
		return $this->getModel()->getPath();
	}
	
	public function getContentType() {
		return 'text/html';
	}

	public function __construct(Processor $processor, Request $request, Model $model, Security $security) {
		$this->processor = $processor;
		$this->request = $request;
		$this->model = $model;
		$this->security = $security;
		
		$this->design = $this->getModel()->getDesign();
		
		$this->load();
		
		$this->design->assign('processor', $this->getProcessor());
		$this->design->assign('request', $this->getRequest());
		$this->design->assign('model', $this->getModel());
		$this->design->assign('base', $this->getBase());
		try {
			$this->design->assign('self', $this->getProcessor()->findLink($this->getProcessor(), $this->getRequest()->getTrail()));
		} catch(LinkNotFoundException $e) {
			// ignore as action doesn't really exist
		}
		$this->design->assign('version', Anomey::VERSION);
	}
	
	protected function load() {
		// to be overloaded
	}
	
	protected function forward($trail, $message = null) {
		if($message != null) {
			$this->getRequest()->addMessage($message);
		}
		
		if(Processor::isRelativeTrail($trail)) {
			$trail = Processor::resolveTrail($this->getBase(), $trail);
		}
		
		$this->getProcessor()->forward($this->getRequest(), $trail, $this->getRequest()->getMessages());
	}
}

abstract class DynamicAction extends Action {
	
	public function execute() {
		$action = $this->getRequest()->getParameter(1);
		
		if(!method_exists($this, $action) or in_array(strtolower($action), array('getprocessor', 'getrequest', 
			'getmodel', 'getsecurity', 'getdesign', 'getbase', 'getcontenttype', '__construct', 
			'load', 'execute', 'forward'))) {
			$action = 'index';
		}
		
		$this->$action();
	}
	
	abstract protected function index();
}

?>
