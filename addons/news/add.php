<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2008 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: enUS.php 1126 2007-07-27 05:14:27Z Zanix $
 * @link       http://www.wowroster.net
 * @package    News
*/

if( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

if( ! $roster->auth->getAuthorized( $addon['config']['news_add'] ) )
{
	echo $roster->auth->getLoginForm($addon['config']['news_add']);

	return; //To the addon framework
}

$roster->output['body_onload'] .= 'initARC(\'addnews\',\'radioOn\',\'radioOff\',\'checkboxOn\',\'checkboxOff\');';

// Assign template vars
$roster->tpl->assign_vars(array(
	'L_ADD_NEWS'     => $roster->locale->act['add_news'],
	'L_NAME'         => $roster->locale->act['name'],
	'L_TITLE'        => $roster->locale->act['title'],
	'L_ENABLE_HTML'  => $roster->locale->act['enable_html'],
	'L_DISABLE_HTML' => $roster->locale->act['disable_html'],

	'S_HTML_ENABLE' => false,
	'S_NEWS_HTML'   => $addon['config']['news_html'],

	'U_FORMACTION'  => makelink('util-news'),
	)
);

if($addon['config']['news_html'] >= 0)
{
	$roster->tpl->assign_var('S_HTML_ENABLE',true);

	if($addon['config']['news_nicedit'] > 0)
	{
		$roster->output['html_head'] .= '<script type="text/javascript" src="' . ROSTER_PATH . 'js/nicEdit.js"></script>
<script type="text/javascript">
	bkLib.onDomLoaded(function() { nicEditors.allTextAreas({xhtml : true, fullPanel : true, iconsPath : \'' . $roster->config['img_url'] . 'nicEditorIcons.gif\'}) });
</script>';
	}
}

$roster->tpl->set_filenames(array('body' => $addon['basename'] . '/add.html'));
$roster->tpl->display('body');
