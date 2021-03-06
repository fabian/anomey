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
function smarty_block_layout($params, $content, & $smarty, &$repeat) {
	if (is_null($content)) {
		return;
	}

	if (!isset ($params['template'])) {
		$smarty->trigger_error("layout: missing 'template' parameter");
		return;
	} else {
		$template = $params['template'];
		unset ($params['template']);
		
		$smarty->assign('content', $content);
		
		foreach($params as $name => $value) {
			$smarty->assign($name, $value);
		}
        
		return $smarty->fetch($template);
	}

}
?>
