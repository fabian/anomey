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

require_once 'util.package.php';
require_once 'action.package.php';

abstract class Form extends Bean {

	private $errors = array ();
	
	public function addError(Message $error) {
		$this->errors[] = $error;
	}

	public function offsetGet($key) {
		return $this->$key;
	}

	protected function assertNotEmpty($value, Message $error) {
		if ($value == '') {
			$this->addError($error);
			return false;
		} else {
			return true;
		}
	}

	protected function assertNotNull($value, Message $error) {
		if ($value == '') {
			$this->addError($error);
			return false;
		} else {
			return true;
		}
	}

	protected function assertEqual($value, $value2, Message $error) {
		if ($value != $value2) {
			$this->addError($error);
			return false;
		} else {
			return true;
		}
	}

	protected function assertNotEqual($value, $value2, Message $error) {
		if ($value == $value2) {
			$this->addError($error);
			return false;
		} else {
			return true;
		}
	}

	protected function assertTrue($condition, Message $error) {
		if (!$condition) {
			$this->addError($error);
			return false;
		} else {
			return true;
		}
	}

	protected function assertFalse($condition, Message $error) {
		if ($condition) {
			$this->addError($error);
			return false;
		} else {
			return true;
		}
	}

	protected function assertNumeric($value, Message $error) {
		if (is_numeric($value)) {
			return true;
		} else {
			$this->addError($error);
			return false;
		}
	}

	protected function assertRegEx($value, $expression, Message $error) {
		if (!preg_match($expression, $value)) {
			$this->addError($error);
			return false;
		} else {
			return true;
		}
	}

	protected function assertInList($value, $list, Message $error) {
		if (array_search($value, $list) === false) {
			$this->addError($error);
			return false;
		} else {
			return true;
		}
	}

	protected function assertNotInList($value, $list, Message $error) {
		if (array_search($value, $list) === false) {
			return true;
		} else {
			$this->addError($error);
			return false;
		}
	}

	protected abstract function validate();

	public function check() {
		$this->validate();
		return $this->getErrors();
	}

	public function hasErrors() {
		if (count($this->getErrors()) > 0) {
			return true;
		} else {
			return false;
		}
	}

	public function getErrors() {
		return $this->errors;
	}
}

abstract class FormAction extends Action {
	private $form;

	public function getForm() {
		return $this->form;
	}

	protected abstract function getTemplate();

	protected abstract function createForm();

	protected function loadForm(Form $form) {
		
	}

	protected abstract function succeed(Form $form);

	protected function getReturn() {
		return $this->getRequest()->getTrail();
	}

	protected function cancel() {
		$this->forward($this->getReturn());
	}

	protected function error($errors) {
		$this->getRequest()->addMessages($errors);
		$this->display();
	}

	protected function display($message = null) {
		// TODO check compability Action::display
		
		if ($message != null) {
			$this->getRequest()->addMessage($message);
		}

		$this->getDesign()->assign('form', $this->getForm());
		parent::display($this->getTemplate());
	}

	public final function execute() {
		$this->load(); // TODO check if this is needed
		$this->form = $this->createForm();

		if ($this->getRequest()->getParameter('cancel', false) !== false) {
			$this->cancel();
		} else {
			if ($this->getRequest()->getParameter('submit', false) !== false && 
				$this->getRequest()->getMethod() == Request::METHOD_POST) {

				// Fill form values with parameter
				$parameters = $this->getRequest()->getParameters();
				foreach ($this->form as $key => $value) {
					if (isset ($parameters[$key])) {
						$this->form->$key = HTML :: specialchars($parameters[$key]);
					}
				}
		
				if ($errors = $this->getForm()->check()) {
					$this->error($errors);
				} else {
					$message = $this->succeed($this->getForm());
					$this->forward($this->getReturn(), $message);
				}
			} else {
				$this->loadForm($this->form);
				$this->display();
			}
		}
	}
}
?>
