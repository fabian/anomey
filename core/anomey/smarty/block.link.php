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

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {link}{/link} block plugin
 *
 * Type:     block function<br>
 * Name:     link<br>
 */
function smarty_block_link($params, $content, & $smarty, & $repeat) {
	if (!isset ($params['trail'])) {
		$smarty->trigger_error("link: missing 'trail' parameter");
		return;
	} else {
		$trail = $params['trail'];
		unset ($params['trail']);
		
		$url = Value::get($params['url'], 'false');
		unset ($params['url']);
		
		$trail = Processor::resolveTrail($smarty->get_template_vars('base'), $trail);
		
		if ($repeat) {
			$parameters = array();
			foreach($params as $key=>$parameter) {
				$parameters[] = $key.'='.urlencode($parameter);
			}
			
			$query = implode('&', $parameters);
			
			if($url == 'true') {
				$href = $smarty->get_template_vars('processor')->makeURL($trail, $query);
			} else {
				$href = $smarty->get_template_vars('processor')->makeRelativeURL($trail, $query);
			}

			if(strpos($smarty->get_template_vars('request')->getTrail(), $trail) === 0) {
				$smarty->assign('active', true);
			} else {
				$smarty->assign('active', false);
			}
			$smarty->assign('href', htmlentities($href));
		} else {
			try {
				$link = $smarty->get_template_vars('processor')->getLinkByTrail($trail);
			} catch (LinkNotFoundException $e) {
			}
			
			if($link instanceof SecureLink) {
				if($link->isExecutable($smarty->get_template_vars('request')->getUser())) {
					return $content;
				} else {
					return;
				}
			} else {
				return $content;
			}
		}
	}
}
?>
