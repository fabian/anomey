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

class ErrorHandler {
	
	private $debug;

	public function __construct($debug) {
		$this->debug = $debug;
	}

	public function handle($severity, $message, $file, $line) {
		if (error_reporting() != 0 and $this->debug) {
			if(!(E_NOTICE & $severity)) {
				// ignore
			} elseif(!(E_WARNING & $severity)) {
				echo '<br/><strong>Warning:</strong> ' . $message . ' <em>in file ' . $file . ':' . $line . '</em>';
			} else {
				throw new ErrorException($message, 0, $severity, $file, $line);
			}
		}
	}
}

?>