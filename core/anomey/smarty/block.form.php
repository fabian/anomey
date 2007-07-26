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

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {layout}{/layout} block plugin
 *
 * Type:     block function<br>
 * Name:     layout<br>
 */
function smarty_block_form($params, $content, & $smarty, & $repeat) {

	if (!$repeat) {
		$method = Value::get($params['method'], 'POST');
		$trail = Value::get($params['method'], $smarty->get_template_vars('request')->getTrail());
		$processor = $smarty->get_template_vars('processor');

		$trail = Processor::resolveTrail($smarty->get_template_vars('base'), $trail);

		$href = $smarty->get_template_vars('processor')->makeRelativeURL($trail);

		if(isset($params['enctype'])) {
			return '<form action="' . $href . '" enctype="' . $params['enctype'] . '" method="' . $method . '">' . $content . '</form>';
		} else {
			return '<form action="' . $href . '" method="' . $method . '">' . $content . '</form>';
		}
	}
}
?>
