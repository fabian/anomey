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

if (version_compare(PHP_VERSION, "5.1.3", "<")) {
	exit('You need at least PHP 5.1.3 to use anomey! Your current PHP version is ' . PHP_VERSION . '.');
}

require_once 'core/smarty/Smarty.class.php';
require_once 'core/anomey/anomey.inc.php';

?>