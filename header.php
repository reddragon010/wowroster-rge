<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Overall header for Roster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2008 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: header.php 1791 2008-06-15 16:59:24Z Zanix $
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.8.0
 * @package    WoWRoster
*/

if( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

define('ROSTER_HEADER_INC',true);

/**
 * Detect and set headers
 */
if( $roster->output['http_header'] && !headers_sent() )
{
	$now = gmdate('D, d M Y H:i:s', time()) . ' GMT';

	@header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	@header('Last-Modified: ' . $now);
	@header('Cache-Control: no-store, no-cache, must-revalidate');
	@header('Cache-Control: post-check=0, pre-check=0', false);
	@header('Pragma: no-cache');
	@header('Content-type: text/html; charset=utf-8');
}

switch( $roster->scope )
{
	case 'util':
	case 'page':
		$roster_title = ' [ ' . $roster->config['default_name'] . ' ] '
					  . (isset($roster->output['title']) ? $roster->output['title'] : '');
		break;

	case 'realm':
		$roster_title = ' [ ' . $roster->data['region'] . '-' . $roster->data['server'] . ' ] '
					  . (isset($roster->output['title']) ? $roster->output['title'] : '');
		break;

	case 'guild':
	case 'char':
		$roster_title = ' [ ' . $roster->data['guild_name'] . ' @ ' . $roster->data['region'] . '-' . $roster->data['server'] . ' ] '
					  . (isset($roster->output['title']) ? $roster->output['title'] : '');
		break;

	default:
		$roster_title = (isset($roster->output['title']) ? $roster->output['title'] : '');
		break;
}


/**
 * Assign template vars
 */
$roster->tpl->assign_vars(array(
	// These are duplicated since there might be an error before settings.php finishes and sets these
	'XML_LANG'        => substr($roster->config['locale'],0,2),
	'ROSTER_URL'      => ROSTER_URL,
	'ROSTER_PATH'     => ROSTER_PATH,
	'S_HEADER_LOGO'   => ( !empty($roster->config['logo']) ? true : false ),
	'WEBSITE_ADDRESS' => $roster->config['website_address'],
	'U_MAKELINK'      => makelink(),
	'HEADER_LOGO'     => $roster->config['logo'],
	'IMG_URL'         => $roster->config['img_url'],
	// End duplication

	'PAGE_TITLE'      => $roster_title,
	'ROSTER_HEAD'     => $roster->output['html_head'],
	'ROSTER_BODY'     => (!empty($roster->config['roster_bg']) ? ' style="background-image:url(' . $roster->config['roster_bg'] . ');"' : '')
					   . (!empty($roster->output['body_attr']) ? ' ' . $roster->output['body_attr'] : ''),
	'ROSTER_ONLOAD'   => (!empty($roster->output['body_onload']) ? $roster->output['body_onload'] : ''),
	'ROSTER_MENU_BEFORE' => $roster->output['before_menu'],
	)
);


$roster->tpl->set_filenames(array('roster_header' => 'header.html'));
$roster->tpl->display('roster_header');

// BETA ONLY, COMMENT THIS IN RC OR LATER!
/*if( file_exists(ROSTER_BASE . 'valid.inc') )
{
	include(ROSTER_BASE . 'valid.inc');
}*/
// END BETA ONLY
