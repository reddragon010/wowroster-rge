<?php
/**
 * WoWRoster.net WoWRoster
 *
 * The only file anyone should directly access in Roster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2008 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: index.php 1791 2008-06-15 16:59:24Z Zanix $
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.8.0
 * @package    WoWRoster
*/

//---[ Text File Downloader ]-----------------------------
if( isset($_POST['send_file']) && !empty($_POST['send_file']) && !empty($_POST['data']) )
{
	$file = $_POST['data'];

	header('Content-Type: text/x-delimtext; name="' . $_POST['send_file'] . '.txt"');
	header('Content-disposition: attachment; filename="' . $_POST['send_file'] . '.txt"');

	// We need to stripslashes no matter what the setting of magic_quotes_gpc is
	echo stripslashes($file);

	exit;
}

define('IN_ROSTER',true);

require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR . 'settings.php' );

// --[ Get path info based on scope ]--
if( !isset($roster->pages[1]) )
{
	$roster->pages[1] = '';
}

switch( $roster->pages[0] )
{
	case 'char':
		$path = ROSTER_ADDONS . $roster->pages[1] . DIR_SEP . 'char' . DIR_SEP .
			( isset($roster->pages[2]) ? $roster->pages[2] : 'index' ). '.php';
		if( !file_exists($path) )
		{
			$path = ROSTER_ADDONS . $roster->pages[1] . DIR_SEP . 'char' . DIR_SEP . 'index.php';
		}
		break;

	case 'guild':
		$path = ROSTER_ADDONS . $roster->pages[1] . DIR_SEP . 'guild' . DIR_SEP .
			( isset($roster->pages[2]) ? $roster->pages[2] : 'index' ). '.php';
		if( !file_exists($path) )
		{
			$path = ROSTER_ADDONS . $roster->pages[1] . DIR_SEP . 'guild' . DIR_SEP . 'index.php';
		}
		break;

	case 'realm':
		$path = ROSTER_ADDONS . $roster->pages[1] . DIR_SEP . 'realm' . DIR_SEP .
			( isset($roster->pages[2]) ? $roster->pages[2] : 'index' ). '.php';
		if( !file_exists($path) )
		{
			$path = ROSTER_ADDONS . $roster->pages[1] . DIR_SEP . 'realm' . DIR_SEP . 'index.php';
		}
		break;

	case 'util':
		$path = ROSTER_ADDONS . $roster->pages[1] . DIR_SEP .
			( isset($roster->pages[2]) ? $roster->pages[2] : 'index' ). '.php';
		if( !file_exists($path) )
		{
			$path = ROSTER_ADDONS . $roster->pages[1] . DIR_SEP . 'index.php';
		}
		break;

	default:
		// OK, so it isn't a scope. Prolly a file in pages.
		if( file_exists($file = ROSTER_PAGES . $roster->pages[0] . '.php') )
		{
			$content = '';
			ob_start();
				require($file);
			$content = ob_get_clean();

			if( $roster->output['show_header'] )
			{
				include_once(ROSTER_BASE . 'header.php');
			}

			if( $roster->output['show_menu'] )
			{
				$roster_menu = new RosterMenu;
				$roster_menu->makeMenu($roster->output['show_menu']);
			}

			echo $content;

			if( $roster->output['show_footer'] )
			{
				include_once(ROSTER_BASE . 'footer.php');
			}

			exit();
		}
		else
		{
			// Send a 404. Then the browser knows what's going on as well.
			header('HTTP/1.0 404 Not Found');
			roster_die(sprintf($roster->locale->act['module_not_exist'],ROSTER_PAGE_NAME),$roster->locale->act['roster_error']);
		}
}

if( empty($roster->pages[1]) )
{
	// Send a 404. Then the browser knows what's going on as well.
	header('HTTP/1.0 404 Not Found');
	roster_die(sprintf($roster->locale->act['module_not_exist'],ROSTER_PAGE_NAME),$roster->locale->act['roster_error']);
}

$addon = getaddon($roster->pages[1]);

//---[ Check if the module exists ]-----------------------
if( !file_exists($path) )
{
	// Send a 404. Then the browser knows what's going on as well.
	header('HTTP/1.0 404 Not Found');
	roster_die(sprintf($roster->locale->act['module_not_exist'],ROSTER_PAGE_NAME),$roster->locale->act['roster_error']);
}

if( !$roster->auth->getAuthorized($addon['access']) )
{
	roster_die(sprintf($roster->locale->act['addon_no_access'],$addon['basename']),$roster->locale->act['addon_error']);
}

if( $addon['active'] == '1' )
{
	// Check if this addon is in the process of an upgrade and deny access if it hasn't yet been upgraded
	$installfile = $addon['inc_dir'] . 'install.def.php';
	$install_class = $addon['basename'] . 'Install';

	if( file_exists($installfile) )
	{
		include_once($installfile);

		if( class_exists($install_class) )
		{
			$addonstuff = new $install_class;

			// -1 = overwrote newer version
			//  0 = same version
			//  1 = upgrade available

			if( version_compare($addonstuff->version,$addon['version']) )
			{
				roster_die(sprintf($roster->locale->act['addon_upgrade_notice'],$addon['basename']) . '<br /><a href="' . makelink('rostercp-install') . '">'
					. sprintf($roster->locale->act['installer_click_upgrade'],$addon['version'],$addonstuff->version) . '</a>',$roster->locale->act['addon_error']);
			}
			unset($addonstuff);
		}
	}

	// Include addon's locale files if they exist
	foreach( $roster->multilanguages as $lang )
	{
		$roster->locale->add_locale_file($addon['locale_dir'] . $lang . '.php',$lang);
	}

	// Include addon's inc/conf.php file
	if( file_exists($addon['conf_file']) )
	{
		include_once($addon['conf_file']);
	}

	// The addon will now assign its output to $content
	$content = '';
	ob_start();
		require($path);
	$content .= ob_get_clean();


	// Pass all the css to $roster->output['html_head'] which is a placeholder in roster_header for more css style defines
	if( $addon['css_url'] != '' )
	{
		$roster->output['html_head'] .= '<link rel="stylesheet" type="text/css" href="' . $addon['css_url'] . '" />' . "\n";
	}
	if( $addon['tpl_css_url'] != '' )
	{
		$roster->output['html_head'] .= '<link rel="stylesheet" type="text/css" href="' . $addon['tpl_css_url'] . '" />' . "\n";
	}

	if( $roster->output['show_header'] )
	{
		include_once(ROSTER_BASE . 'header.php');
	}

	if( $roster->output['show_menu'] )
	{
		$roster_menu = new RosterMenu;
		$roster_menu->makeMenu($roster->output['show_menu']);
	}

	echo $content;

	if( $roster->output['show_footer'] )
	{
		include_once(ROSTER_BASE . 'footer.php');
	}
}
else
{
	roster_die(sprintf($roster->locale->act['addon_disabled'],$addon['basename']),$roster->locale->act['addon_error']);
}


$roster->db->close_db();
