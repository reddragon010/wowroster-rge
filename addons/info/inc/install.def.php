<?php
/**
 * WoWRoster.net WoWRoster
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2008 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: install.def.php 1794 2008-06-15 21:28:59Z Zanix $
 * @link       http://www.wowroster.net
 * @package    CharacterInfo
 * @subpackage Installer
*/

if ( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

/**
 * Installer for Character Info Addon
 * @package    CharacterInfo
 * @subpackage Installer
 */
class infoInstall
{
	var $active = true;
	var $icon = 'inv_misc_grouplooking';

	var $version = '1.9.9.1747';
	var $wrnet_id = '0';

	var $fullname = 'char_info';
	var $description = 'char_info_desc';
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
		global $installer, $roster;

		// Master and menu entries
		$installer->add_config("'1','startpage','char_conf','display','master'");
		$installer->add_config("'110','char_conf',NULL,'blockframe','menu'");
		$installer->add_config("'120','char_pref','rostercp-addon-info-display','makelink','menu'");

		$installer->add_config("'1000', 'recipe_disp', '0', 'radio{show^1|collapse^0', 'char_conf'");
		$installer->add_config("'1010', 'mail_disp', '1', 'radio{Table^0|Bag^1|Both^2', 'char_conf'");
		$installer->add_config("'1020', 'show_money', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1030', 'show_played', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1040', 'show_tab2', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1050', 'show_tab3', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1060', 'show_tab4', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1070', 'show_tab5', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1080', 'show_talents', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1090', 'show_spellbook', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1100', 'show_mail', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1110', 'show_bags', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1120', 'show_bank', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1130', 'show_quests', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1140', 'show_recipes', '0', 'function{infoAccess', 'char_conf'");
		$installer->add_config("'1150', 'show_item_bonuses', '0', 'function{infoAccess', 'char_conf'");

		$installer->create_table($installer->table('display'),"
		  `member_id` int(11) NOT NULL default '0',
		  `show_money` tinyint(1) NOT NULL default '0',
		  `show_played` tinyint(1) NOT NULL default '0',
		  `show_tab2` tinyint(1) NOT NULL default '0',
		  `show_tab3` tinyint(1) NOT NULL default '0',
		  `show_tab4` tinyint(1) NOT NULL default '0',
		  `show_tab5` tinyint(1) NOT NULL default '0',
		  `show_talents` tinyint(1) NOT NULL default '0',
		  `show_spellbook` tinyint(1) NOT NULL default '0',
		  `show_mail` tinyint(1) NOT NULL default '0',
		  `show_bags` tinyint(1) NOT NULL default '0',
		  `show_bank` tinyint(1) NOT NULL default '0',
		  `show_quests` tinyint(1) NOT NULL default '0',
		  `show_recipes` tinyint(1) NOT NULL default '0',
		  `show_item_bonuses` tinyint(1) NOT NULL default '0',
		  PRIMARY KEY  (`member_id`)");


		$installer->create_table($installer->table('default'),"
		  `show_money` tinyint(1) NOT NULL default '0',
		  `show_played` tinyint(1) NOT NULL default '0',
		  `show_tab2` tinyint(1) NOT NULL default '0',
		  `show_tab3` tinyint(1) NOT NULL default '0',
		  `show_tab4` tinyint(1) NOT NULL default '0',
		  `show_tab5` tinyint(1) NOT NULL default '0',
		  `show_talents` tinyint(1) NOT NULL default '0',
		  `show_spellbook` tinyint(1) NOT NULL default '0',
		  `show_mail` tinyint(1) NOT NULL default '0',
		  `show_bags` tinyint(1) NOT NULL default '0',
		  `show_bank` tinyint(1) NOT NULL default '0',
		  `show_quests` tinyint(1) NOT NULL default '0',
		  `show_recipes` tinyint(1) NOT NULL default '0',
		  `show_item_bonuses` tinyint(1) NOT NULL default '0'");

		$build_query = array(
			'show_money' => '0',
			'show_played' => '0',
			'show_tab2' => '0',
			'show_tab3' => '0',
			'show_tab4' => '0',
			'show_tab5' => '0',
			'show_talents' => '0',
			'show_spellbook' => '0',
			'show_mail' => '0',
			'show_bags' => '0',
			'show_bank' => '0',
			'show_quests' => '0',
			'show_recipes' => '0',
			'show_item_bonuses' => '0'
		);

		$installer->add_query('INSERT INTO `' . $installer->table('default') . '` ' . $roster->db->build_query('INSERT',$build_query) . ';');

		$installer->add_query('INSERT INTO `' . $installer->table('display') . '` SELECT `p`.`member_id` , `d` . * FROM `' . $roster->db->table('players') . '` p, `' . $installer->table('default') . '` d ');

		$installer->add_menu_button('cb_character','char');
		$installer->add_menu_button('cb_talents','char','talents','ability_marksmanship');
		$installer->add_menu_button('cb_spellbook','char','spellbook','inv_misc_book_09');
		$installer->add_menu_button('cb_mailbox','char','mailbox','inv_letter_02');
		$installer->add_menu_button('cb_bags','char','bags','inv_misc_bag_08');
		$installer->add_menu_button('cb_bank','char','bank','inv_misc_bag_15');
		$installer->add_menu_button('cb_quests','char','quests','inv_misc_note_02');
		$installer->add_menu_button('cb_recipes','char','recipes','inv_scroll_02');

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
		global $installer, $roster;

		// Basicly we are re-installing this addon, since the config section has changed so much
		// This means any version upgrade routines are not needed
		if( version_compare('1.9.9.1745', $oldversion,'>') == true )
		{
			$installer->remove_all_config();
			$installer->remove_all_menu_button();

			$installer->add_query("ALTER TABLE `" . $roster->db->table('players') . "`
			  DROP `show_money`,
			  DROP `show_played`,
			  DROP `show_tab2`,
			  DROP `show_tab3`,
			  DROP `show_tab4`,
			  DROP `show_tab5`,
			  DROP `show_talents`,
			  DROP `show_spellbook`,
			  DROP `show_mail`,
			  DROP `show_bags`,
			  DROP `show_bank`,
			  DROP `show_quests`,
			  DROP `show_recipes`,
			  DROP `show_item_bonuses`;");

			// Important to return here, a re-install updates to the latest version no matter what, no upgrades needed
			return $this->install();
		}

		// Basicly I screwed up on the table naming here
		if( version_compare('1.9.9.1747', $oldversion,'>') == true )
		{
			$installer->add_query('RENAME TABLE `' . $installer->table('') . '`  TO `' . $installer->table('display') . '` ;');
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
		global $installer, $roster;

		$installer->remove_all_config();
		$installer->remove_all_menu_button();
		$installer->drop_table($installer->table('display'));
		$installer->drop_table($installer->table('default'));

		return true;
	}
}
