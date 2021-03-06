<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2008 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: install.def.php 1791 2008-06-15 16:59:24Z Zanix $
 * @link       http://www.wowroster.net
 * @package    GuildInfo
 * @subpackage Installer
*/

if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

/**
 * Installer for GuildInfo Addon
 * @package    GuildInfo
 * @subpackage Installer
 */
class guildinfoInstall
{
	var $active = true;
	var $icon = 'inv_misc_note_06';

	var $version = '1.9.9.1758';
	var $wrnet_id = '0';

	var $fullname = 'guildinfo';
	var $description = 'guildinfo_desc';
	var $credits = array(
		array(	"name"=>	"WoWRoster Dev Team",
				"info"=>	"Original Author")
	);


	/**
	 * Install Function
	 *
	 * @return bool
	 */
	function install()
	{
		global $installer;

		$installer->add_menu_button('ginfobutton','guild');
		return true;
	}

	/**
	 * Upgrade Function
	 *
	 * @param string $oldversion
	 * @return bool
	 */
	function upgrade($oldversion)
	{
		global $installer;

		if( version_compare( $oldversion, '1.9.9.1562', '<' ) )
		{
			$installer->add_config("'1','startpage','guildinfo_conf','display','master'");
			$installer->add_config("'100','guildinfo_conf',NULL,'blockframe','menu'");
			$installer->add_config("'1000', 'guildinfo_access', '0', 'access', 'guildinfo_conf'");
		}

		if( version_compare( $oldversion, '1.9.9.1758', '<' ) )
		{
			$installer->remove_all_config();
		}

		return true;
	}

	/**
	 * Un-Install Function
	 *
	 * @return bool
	 */
	function uninstall()
	{
		global $installer;

		$installer->remove_all_menu_button();
		return true;
	}
}
