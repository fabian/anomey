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

class Extension {

	private $site;

	public function getSite() {
		return $this->site;
	}
	
	public function setSite(Site $site) {
		$this->site = $site;
	}

	private $processor;

	public function getProcessor() {
		return $this->processor;
	}
	
	public function setProcessor(Processor $processor) {
		$this->processor = $processor;
	}

	/**
	 * To be overloaded.
	 */
	public function load() {

	}

	/**
	 * To be overloaded.
	 */
	public function invoke(Request $request) {

	}

	/**
	 * To be overloaded.
	 */
	public function finish(Response $response) {

	}
}

?>