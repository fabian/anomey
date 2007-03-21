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

class Controller {
	
	const profiles = 'profiles';
	
	public static function handle($base = '') {
		// Create profiles folder.
		if(!self :: createFolder(self :: profiles)) {
			exit('Could not create folder <em>' . self :: profiles . '</em> inside the anomey folder!');
		}
		
		$host = $_SERVER['HTTP_HOST'];
		
		$profile = '';
		if(file_exists(self :: profiles . '/' . $host)) {
			$profile = self :: profiles . '/' . $host;
		} else {
			$profile = self :: profiles . '/default';
		}
		
		new self($profile, $base);
	}
	
	private $profile;
	
	public function __construct($profile, $base) {
		try {
			ob_start();
			
			$this->profile = $profile;
	
			// -----------------------------
			// Create profile folder.
			// -----------------------------
			if(@!self :: createFolder($profile)) {
				exit('<strong>Error:</strong> Could not create profile folder <em>' . $profile . '</em>! Please check permissions.');
			}
	
			// -----------------------------
			// Create config folder.
			// -----------------------------
			$this->createProfileFolder('config', true);
			file_put_contents($profile . '/config/debug.ini', 'enabled=false');
			
			$debug = Configuration::load($profile . '/config/debug.ini');
			
			$errorhandler = new ErrorHandler($debug->enabled);
			set_error_handler(array($errorhandler, 'handle'));
	
			// -----------------------------
			// Create tmp folder.
			// -----------------------------
			$this->createProfileFolder('tmp', true);
	
			// -----------------------------
			// Create xml folder.
			// -----------------------------
			$this->createProfileFolder('xml', true);
	
			// -----------------------------
			// Create extensions folder.
			// -----------------------------
			$this->createProfileFolder('extensions');
	
			// -----------------------------
			// Create designs folder.
			// -----------------------------
			$this->createProfileFolder('designs');
	
			// -----------------------------
			// Create modules folder.
			// -----------------------------
			$this->createProfileFolder('modules');
			
			// -----------------------------
			// Instantiate the base URL
			// -----------------------------
	
			// Find out the current schemata.
			$serverHttpsEnabled = isset ($_SERVER["HTTPS"]) ? $_SERVER["HTTPS"] : 'off';
			if (strtolower($serverHttpsEnabled) == 'on') {
				$scheme = 'https';
			} else {
				$scheme = 'http';
			}
	
			// Read the server host.
			$host = $_SERVER['HTTP_HOST'];
	
			// Read the path of the script (whoever made this necessary should get fired)
			$script = basename($_SERVER['SCRIPT_FILENAME']);
			if (basename($_SERVER['PHP_SELF']) === $script) {
				$path = str_replace('\\', '/', dirname($_SERVER['PHP_SELF']));
			} elseif (basename($_SERVER['SCRIPT_NAME']) === $script) {
				$path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
			} elseif (basename($_SERVER['ORIG_SCRIPT_NAME']) === $script and isset($_SERVER['ORIG_SCRIPT_NAME'])) {
				$path = str_replace('\\', '/', dirname($_SERVER['ORIG_SCRIPT_NAME']));
			} else {
				$path = '';
			}
	
			// Add a slash to the end of the path if
			// anomey doesn't run in the root folder
			$path .= $path != '/' ? '/' : '';
	
			$url = new URL($scheme, $host, $path, $base);
	
			// -----------------------------
			// Instantiate the security. 
			// -----------------------------
	
			$security = new Security($profile);
	
			// -----------------------------
			// Instantiate the site. 
			// -----------------------------
	
			$site = $security->getObject(Site :: ID);
			$site->invoke($profile . '/xml/sitemap.xml', $security);
	
			// -----------------------------
			// Instantiate the request.
			// -----------------------------
	
			// Read the request method.
			$method = $_SERVER['REQUEST_METHOD'];
	
			// Trick out a CGI bug
			if (isset ($_SERVER['PATH_INFO'])) {
				if ($_SERVER['PATH_INFO'] == '' AND isset ($_SERVER['ORIG_PATH_INFO'])) {
					$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
				} elseif ($_SERVER['PATH_INFO'] == '') {
					$_SERVER['PATH_INFO'] = '/';
				}
			}
	
			// Find out the trail.
			$trail = Value :: get($_SERVER['PATH_INFO'], '/');
	
			// Merge the parameters passed over POST and GET.
			$parameters = array_merge($_POST, $_GET);
	
			// Wipe out nasty php quotes ...
			if (get_magic_quotes_gpc()) {
				$parameters = String :: stripslashes($parameters);
			}
	
			// Initialize session.
			$session = new Session();
	
			// Load and clear messages.
			$messages = $session->load('systemMessages', array ());
			$session->clear('systemMessages');
	
			// Initialize cookie.
			$cookie = new Cookie($url);
	
			// Load user from session.
			$userId = $session->load('systemUser');
			try {
				$user = $security->getUser($userId);
				$user->setTrusted($session->load('systemUserTrusted', false));
			} catch (UserNotFoundException $e) {
				$user = null;
			}
	
			// Try to load user from cookies
			$userId = $cookie->get('systemUser');
			$userToken = $cookie->get('systemUserToken');
			try {
				$cookieUser = $security->token($userId, $userToken);
	
				if ($cookieUser != null) {
					if ($user == null) {
						$user = $cookieUser;
						$session->store('systemUser', $user->getId());
					}
	
					// Update cookie to expire after a month
					$expire = strtotime('+ 1 month');
					$cookie->store('systemUser', $userId, $expire);
					$cookie->store('systemUserToken', $userToken, $expire);
	
					// Update saved token
					$security->updateToken($user, $userToken);
				}
			} catch (WrongTokenException $e) {
			}
			// Clear unnecessary tokens
			$security->clearTokens();
	
			$request = new Request($user, $method, $trail, $session, $cookie, $parameters, $messages);
	
			// Initialize response.
			$response = new Response();
			
			// -----------------------------
			// Call the processor. 
			// -----------------------------
	
			$processor = new Processor($url, $profile . '/xml/config.xml', $security, $site);
			$processor->execute($request, $response);
				
			// -----------------------------
			// Write out the response.
			// -----------------------------

			header('Content-type: ' . $response->getContentType());
			echo $response->getBody();
		} catch (Exception $e) {
			$code = ob_get_clean();
			include 'error.view.php';
		}
		ob_end_flush();
	}
	
	private static function createFolder($name, $protected = false) {
		if (!file_exists($name)) {
			$return = mkdir($name);
			if($protected) {
				file_put_contents($name . '/.htaccess', 'Deny from all');
			}
			return $return;
		}
		return true;
	}
	
	private function createProfileFolder($name, $protected = false) {
		return self::createFolder($this->profile . '/' . $name, $protected);
	}
}
?>
