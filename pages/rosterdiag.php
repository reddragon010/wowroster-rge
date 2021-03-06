<?php
/**
 * WoWRoster.net WoWRoster
 *
 * Roster Diagnostics and info
 *
 * LICENSE: Licensed under the Creative Commons
 *          "Attribution-NonCommercial-ShareAlike 2.5" license
 *
 * @copyright  2002-2008 WoWRoster.net
 * @license    http://creativecommons.org/licenses/by-nc-sa/2.5   Creative Commons "Attribution-NonCommercial-ShareAlike 2.5"
 * @version    SVN: $Id: rosterdiag.php 1791 2008-06-15 16:59:24Z Zanix $
 * @link       http://www.wowroster.net
 * @since      File available since Release 1.8.0
 * @package    WoWRoster
 * @subpackage RosterDiag
*/

if( !defined('IN_ROSTER') )
{
    exit('Detected invalid access to this file!');
}

// Set the title for the header
$roster->output['title'] = $roster->locale->act['rosterdiag'];

// Include the library for RosterDiag
include_once(ROSTER_LIB.'rosterdiag.lib.php');

echo '<span class="title_text">' . $roster->locale->act['rosterdiag'] . '</span>';

// Loging in as Admin to allow up- / downgrades && Downloads

// If the entire config page is requested, display only THAT
if( isset($_GET['printconf']) && $_GET['printconf'] == 1 )
{
	echo '<div align="left">';
	aprint($roster->config);
	echo '</div>';

	return;
}

// If a FileDiff is requested, display the header of the file and display Warning / Confirmation
if(isset($_POST['filename']) && isset($_POST['downloadsvn']))
{
	if ($_POST['downloadsvn'] == 'confirmation')
	{
		//Do confirmation stuff
		$filename = $_POST['filename'];
		if (is_file($filename))
		{
			$md5local = md5_file($filename);
		}
		else
		{
			$md5local = "Local File does not exist yet";
		}

		$md5remote = urlgrabber(ROSTER_SVNREMOTE.'?getfile='.$filename.'&mode=md5');
		if ($md5remote===false)
		{
			roster_die("[ERROR] Cannot Read MD5 Remote File\n");
		}

		$filesvnsource = urlgrabber(ROSTER_SVNREMOTE.'?getfile='.$filename.'&mode=diff');
		if ($filesvnsource===false)
		{
			roster_die("[ERROR] Cannot Read Remote File\n");
		}

		if (file_exists($filename) && is_file($filename) && filesize($filename))
		{
			$rhheaderlocal = fopen($filename, 'rb');
			if ($rhheaderlocal===false)
			{
				roster_die("[ERROR] Cannot Read Local File\n");
			}
			else
			{
				$filelocalsource = '';
				while (!feof($rhheaderlocal))
				{
					$filelocalsource .= fread($rhheaderlocal, filesize($filename));
				}
			}
			fclose($rhheaderlocal);


			// Perform a DIFF check on the local and remote file
			if (check_if_image($filename))
			{
				$svnurl = parse_url(ROSTER_SVNREMOTE);
				$svnpath = pathinfo($svnurl['path'], PATHINFO_DIRNAME);
				$svnurl = $svnurl['scheme'].'://'.$svnurl['host'].$svnpath.'/';
				$diffcheck = '<table width="100%" border="0" cellspacing="0" class="bodyline"><tr><th class="membersHeader">Local Image</th><th class="membersHeaderRight">SVN Image</th></tr>';
				$diffcheck .= '<tr><td class="membersRow1"><img src="'.$filename.'" alt="Local Image" /></td><td class="membersRowRight1"><img src="'.$svnurl.$filename.'" alt="SVN Image" /></td></tr>';
				$diffcheck .= '</table>';
			}
			else
			{
				$diffcheck = '<table width="100%" border="0" cellspacing="0" class="bodyline"><tr><th class="membersHeader">Type</th><th class="membersHeader">Local File</th><th class="membersHeaderRight">SVN File</th></tr>';
				$difffiles = difffile($filelocalsource, $filesvnsource);
				$row_color=2;
				foreach ($difffiles as $difference)
				{
					if($row_color==1)
					{
						$row_color=2;
					}
					else
					{
						$row_color=1;
					}

					$rowfile1 = explode(",", $difference['rownr1']);
					$rowfile2 = explode(",", $difference['rownr2']);

					$diffcheck .= "<tr valign=\"top\">";

					$diffcheck .= '<td class="membersRow'.$row_color.'">';
					$diffcheck .= '<span class="'.$difference['color'].'">'.$difference['action'].'</span>';

					$diffcheck .= '</td><td class="membersRow'.$row_color.'">';


					if (isset($difference['from']))
					{
						$diffcheck .= highlight_php(implode("\n",$difference['from']), $rowfile1[0]);
					}
					$diffcheck .= '</td><td class="membersRowRight'.$row_color.'">';
					if (isset($difference['to']))
					{
						$diffcheck .= highlight_php(implode("\n",$difference['to']), $rowfile2[0]);
					}
					$diffcheck .= '</td>';
					$diffcheck .= '</tr>';
				}
				$diffcheck .= '</table>';
			}
		}
		else
		{
			if (check_if_image($filename))
			{
				$svnurl = parse_url(ROSTER_SVNREMOTE);
				$svnpath = pathinfo($svnurl['path'], PATHINFO_DIRNAME);
				$svnurl = $svnurl['scheme'].'://'.$svnurl['host'].$svnpath.'/';
				$diffcheck = '<table width="100%" border="0" cellspacing="0">'
						   . '<tr><th class="membersHeaderRight">SVN Image</th></tr>'
						   . '<tr><td class="membersRowRight1"><img src="'.$svnurl.$filename.'" alt="" /></td></tr>'
						   . '<tr><td class="membersRowRight2">&nbsp;</td></tr></table>';
			}
			else
			{
				$diffcheck = '<table width="100%" border="0" cellspacing="0">'
						   . '<tr><th class="membersHeaderRight">SVN File</th></tr>'
						   . '<tr><td class="membersRowRight1">'.highlight_php(str_replace("\r\n","\n",$filesvnsource)).'</td></tr>'
						   . '</table>';
			}
		}

		print '<table border="0" cellspacing="6"><tr><td valign="top" align="right">'."\n";

		print border('syellow','start','MD5 Information for file: '.$filename)."\n";
		print '<table width="100%" cellspacing="0" border="0" class="bodyline">';
		print '<tr><td class="membersRow1">Remote:</td><td class="membersRowRight1">'.$md5remote."</td></tr>\n";
		print '<tr><td class="membersRow2">Local:</td><td class="membersRowRight2">'.$md5local."</td></tr>\n";
		print "</table>\n";
		print border('syellow','end');

		print '</td><td>&nbsp;</td><td valign="top" align="left">';

		print border('sblue','start','Back Link');
		print '<table width="100%" cellspacing="0" border="0" class="bodyline">';
		print '<tr><td class="membersRowRight2"><form method="post" action="'.makelink().'">';
		print '<input type="hidden" name="filename" value="'.$filename.'" />';
		print '<input type="hidden" name="downloadsvn" value="savefile" />';
		print '<input type="button" value="[ RETURN TO ROSTERDIAG ]" onclick="history.go(-1);return false;" />';
		print '</form></td></tr></table>';
		print border('sblue','end');

		print '</td></tr></table><br />' ;

		if (isset($_POST['downmode']) && $_POST['downmode'] == 'install')
		{
			$diffwindow = 'File Contents:&nbsp;&nbsp;';
		}
		else
		{
			$diffwindow = 'File Differences for file:&nbsp;&nbsp;';
		}
		print border('sblue','start',$diffwindow.$filename,'90%');
		print $diffcheck;
		print border('sblue','end');

	}
	else
	{
		roster_die('If you get this page, you probably are trying to exploit the system!','UNSPECIFIED ACTION');
	}

	return;
}

// Diplay Password Box
if( ! $roster->auth->getAuthorized( ROSTERLOGIN_ADMIN ) )
{
	echo '<br />' . $roster->auth->getLoginForm(ROSTERLOGIN_ADMIN);
}

echo "<br />\n";

// Display config errors
echo ConfigErrors();

echo "<br />\n";

// Table display fix
echo "<table cellspacing=\"6\"><tr><td valign=\"top\">\n";

// Display basic server info
$rowstripe = 0;
echo messageboxtoggle('
<table width="100%" class="bodyline" cellspacing="0">
	<tr>
		<td class="membersRow'.((($rowstripe=0)%2)+1).'">OS</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.php_uname('s').'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">Server Software</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'" style="white-space:normal;">'.$_SERVER['SERVER_SOFTWARE'].'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">MySQL Version</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.$roster->db->server_info().'</td>
	</tr>
</table>','Basic Server Info','syellow',false,'350px').'
<br />
'.
messageboxtoggle('
<table width="100%" class="bodyline" cellspacing="0">
	<tr>
		<td class="membersRow'.((($rowstripe=0)%2)+1).'">PHP Version</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.PHP_VERSION.'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">PHP API Type</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.php_sapi_name().'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">safe_mode</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.onOffRev(ini_get('safe_mode')).'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">open_basedir</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.onOffRev(ini_get('open_basedir')).'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">allow_url_fopen</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.onOff(ini_get('allow_url_fopen')).'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">file_uploads</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.onOff(ini_get('file_uploads')).'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">upload_max_filesize</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.ini_get('upload_max_filesize').'</td>
	</tr>
</table>','PHP Settings','syellow',false,'350px');



// Table display fix
echo "</td><td valign=\"top\">\n";



// Display GD info
echo messageboxtoggle(describeGDdyn(),'GD Support','sgreen',false,'350px');


// Table display fix
echo "</td></tr></table>\n";
echo "<table cellspacing=\"6\"><tr><td valign=\"top\">\n";

// Display conf.php info

echo messageboxtoggle('
<table width="100%" class="bodyline" cellspacing="0">
	<tr>
		<th colspan="2" class="membersHeaderRight"><i><a href="'.makelink('rosterdiag&amp;printconf=1').'" target="_blank">Show Entire $roster->config array</a></i></th>
	</tr>
	<tr>
		<td class="membersRow'.((($rowstripe=0)%2)+1).'">version</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.$roster->config['version'].'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">db_version</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.$roster->config['roster_dbver'].'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">db_prefix</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.$roster->db->prefix.'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">debug_mode</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.onOffRev($roster->config['debug_mode']).( $roster->config['debug_mode'] == 2 ? ' (extended)' : '' ).'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">roster_lang</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.$roster->config['locale'].'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">img_url</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.$roster->config['img_url'].'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">interface_url</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.$roster->config['interface_url'].'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">img_suffix</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.$roster->config['img_suffix'].'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">use_update_triggers</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.onOff($roster->config['use_update_triggers']).'</td>
	</tr>
	<tr>
		<td class="membersRow'.(((++$rowstripe)%2)+1).'">rs_mode</td>
		<td class="membersRowRight'.((($rowstripe)%2)+1).'">'.onOff($roster->config['rs_mode']).'</td>
	</tr>
</table>','Config Values','sblue',false,'350px')."
<br />\n";


// Table display fix
echo "</td><td valign=\"top\">\n";


// Display MySQL Tables
$sql_tables = '<table width="100%" class="bodyline" cellspacing="0">'."\n";

$result = $roster->db->query("SHOW TABLES;");
if( !$result )
{
	$sql_tables .= '<tr><td class="membersRow1">DB Error, could not list tables<br />'."\n";
	$sql_tables .= 'MySQL Error: '.$roster->db->error().'</td></tr>'."\n";
}
else
{
	$rowstripe = 1;
	while( $row = $roster->db->fetch($result) )
	{
		$sql_tables .= '<tr><td class="membersRowRight'.(((++$rowstripe)%2)+1).'">'.$row[0].'</td></tr>'."\n";
	}
	$roster->db->free_result($result);
}
$sql_tables .= "</table>\n";
echo messageboxtoggle($sql_tables,'List of Tables','',false,'350px');


// Table display fix
echo "</td></tr></table>\n<br />\n";

// File Versioning Information
if( GrabRemoteVersions() !== false )
{
	//GrabRemoteVersions();
	VerifyVersions();

	$zippackage_files = '';

	// Make a post form for the download of a Zip Package
	foreach ($directories as $directory => $filecount)
	{
		if (isset($files[$directory]))
		{
			foreach ($files[$directory] as $file => $filedata)
			{
				if($filedata['update'])
				{
					if (isset($file) && $file != 'newer' && $file != 'severity' && $file != 'tooltip' && $file != 'rollup' && $file != 'rev' && $file != 'date' && $file != 'author' && $file != 'md5' && $file != 'update' && $file != 'missing')
					{
						if ($zippackage_files != '')
						{
							$zippackage_files .= ';';
						}
						$zippackage_files .= $directory.'/'.$file;
					}
				}
			}
		}
	}

	if( $zippackage_files != '' )
	{
		if( ! $roster->auth->getAuthorized( ROSTERLOGIN_ADMIN ) )
		{
			echo messagebox('Log in as Roster Admin to download update files','Updates Available!','spurple');
			echo '<br />';
		}
		else
		{
			echo border('spurple', 'start', '<span class="blue">Download Update Package</span>');
			echo '<div align="center" style="background-color:#1F1E1D;"><form method="post" action="'.ROSTER_SVNREMOTE.'">';
			echo '<input type="hidden" name="filestoget" value="'.$zippackage_files.'" />';
			echo '<input type="hidden" name="guildname" value="'.$roster->config['default_name'].'" />';
			echo '<input type="hidden" name="website" value="'.$roster->config['website_address'].'" />';
			echo '<input type="radio" name="ziptype" id="zip" value="zip" checked="checked" /><label for="zip">.zip Archive</label><br />';
			echo '<input type="radio" name="ziptype" id="targz" value="targz" /><label for="targz">.tar.gz Archive</label><br /><br />';
			echo '<input style="decoration:bold;" type="submit" value="[GET UPDATE PACKAGE]" /><br />';
			echo '</form></div>';
			echo border('spurple', 'end').'<br />';
		}
	}

	// Open the main FileVersion table in total color
	echo border('sgray', 'start', '<span class="blue">File Versions:</span> <small style="color:#6ABED7;font-weight:bold;"><i>Roster File Validator @ '.str_replace('version_match.php', '', ROSTER_SVNREMOTE).'</i></small>');

	// Get all the gathered information and display it in a table
	foreach ($directories as $directory => $filecount)
	{
		if (isset($files[$directory]))
		{
			//echo $directory.', '.$files[$directory]['tooltip'].'<br>';
			$dirtooltip = str_replace("'", "\'", $files[$directory]['tooltip']);
			$dirtooltip = str_replace('"','&quot;', $dirtooltip);
			$directory_id = str_replace(array('.','/','\\'),'', $directory);

			$dirshow = substr_replace($directory, substr(ROSTER_PATH,1,-1), 0, 1);


			$headertext_max = '<div style="cursor:pointer;width:800px;text-align:left;" onclick="swapShow(\''.$directory_id.'TableShow\',\''.$directory_id.'TableHide\')" '
							. 'onmouseover="overlib(\''.$dirtooltip.'\',CAPTION,\''.$dirshow.'/&nbsp;&nbsp;-&nbsp;&nbsp;'.$severity[$files[$directory]['rollup']]['severityname'].'\',WRAP);" onmouseout="return nd();">'
							. '<div style="float:right;"><span style="color:'.$severity[$files[$directory]['rollup']]['color'].';">'.$severity[$files[$directory]['rollup']]['severityname'].'</span> <img class="membersRowimg" src="'.$roster->config['theme_path'].'/images/plus.gif" alt="" /></div>'.$dirshow.'/</div>';

			$headertext_min = '<div style="cursor:pointer;width:800px;text-align:left;" onclick="swapShow(\''.$directory_id.'TableShow\',\''.$directory_id.'TableHide\')" '
							. 'onmouseover="overlib(\''.$dirtooltip.'\',CAPTION,\''.$dirshow.'/&nbsp;&nbsp;-&nbsp;&nbsp;'.$severity[$files[$directory]['rollup']]['severityname'].'\',WRAP);" onmouseout="return nd();">'
							. '<div style="float:right;"><span style="color:'.$severity[$files[$directory]['rollup']]['color'].';">'.$severity[$files[$directory]['rollup']]['severityname'].'</span> <img class="membersRowimg" src="'.$roster->config['theme_path'].'/images/minus.gif" alt="" /></div>'.$dirshow.'/</div>';


			echo '<div style="display:none;" id="'.$directory_id.'TableShow">';
			echo border($severity[$files[$directory]['rollup']]['style'],'start',$headertext_min);


			echo '<table width="100%" cellpadding="0" cellspacing="0" class="bodyline">';
			echo '<tr><th class="membersHeader">Filename</th><th class="membersHeader">Revision</th><th class="membersHeader">Date</th><th class="membersHeader">Author</th><th class="membersHeader">MD5 Match</th><th class="membersHeaderRight">SVN</th>';
			echo '</tr>';
			$row=0;
			foreach ($files[$directory] as $file => $filedata)
			{
				if ($row==1)
					$row=2;
				else
					$row=1;

				if (isset($filedata['tooltip']))
				{
					$filetooltip = str_replace("'", "\'", $filedata['tooltip']);
					$filetooltip = str_replace('"','&quot;', $filetooltip);
				}
				else
				{
					$filetooltip = 'Unknown';
				}
				if (isset($file) && $file != 'newer' && $file != 'severity' && $file != 'tooltip' && $file != 'rollup' && $file != 'rev' && $file != 'date' && $file != 'author' && $file != 'md5' && $file != 'update' && $file != 'diff' && $file != 'missing')
				{
					echo '<tr style="cursor:help;" onmouseover="overlib(\'<span style=&quot;color:blue;&quot;>'.$filetooltip.'</span>\',CAPTION,\''.$file.'/&nbsp;&nbsp;-&nbsp;&nbsp;'.$severity[$filedata['rollup']]['severityname'].'\',WRAP);" onmouseout="return nd();">';
					echo '<td class="membersRow'.$row.'"><span style="color:'.$severity[$filedata['rollup']]['color'].'">'.$file.'</span></td>';
					echo '<td class="membersRow'.$row.'">'."\n";
					if (isset($filedata['rev']))
					{
						echo $filedata['rev'];
					}
					else
					{
						echo 'Unknown Rev';
					}
					echo "</td>\n";
					echo '<td class="membersRow'.$row.'">';
					if (isset($filedata['date']))
					{
						echo $filedata['date'];
					}
					else
					{
						echo 'Unknown Date';
					}
					echo "</td>\n";
					echo '<td class="membersRow'.$row.'">';
					if (isset($filedata['author']))
					{
						echo $filedata['author'];
					}
					else
					{
						echo 'Unknown Author';
					}
					echo "</td>\n";
					echo '<td class="membersRow'.$row.'">';
					if (isset($filedata['md5']))
					{
						echo $filedata['md5'];
					}
					else
					{
						echo 'Unknown';
					}
					echo "</td>\n";
					echo '<td class="membersRowRight'.$row.'">'."\n";
					if($filedata['diff'] || $filedata['missing'])
					{
						echo '<form method="post" action="'.makelink().'">'."\n";
						echo "<input type=\"hidden\" name=\"filename\" value=\"".$directory.'/'.$file."\" />\n";
						echo "<input type=\"hidden\" name=\"downloadsvn\" value=\"confirmation\" />\n";
						if (isset($filedata['diff']) && $filedata['diff'])
						{
							echo "<input type=\"hidden\" name=\"downmode\" value=\"update\" />\n";
							echo "<input type=\"submit\" value=\"Diff Check\" />\n";
						}
						elseif (isset($filedata['missing']) && $filedata['missing'])
						{
							echo "<input type=\"hidden\" name=\"downmode\" value=\"install\" />\n";
							echo "<input type=\"submit\" value=\"Show File\" />\n";
						}
						echo '</form>';

					}
					else
					{
						echo '&nbsp;';
					}
					echo "</td>\n";
					echo "</tr>\n";
				}
			}

			echo '</table>';

			echo border($severity[$files[$directory]['rollup']]['style'],'end').'</div>';
			echo '<div id="'.$directory_id.'TableHide">';
			echo border($severity[$files[$directory]['rollup']]['style'],'start',$headertext_max);
			echo border($severity[$files[$directory]['rollup']]['style'],'end').'</div>';
		}
	}
	echo border('sgray', 'end');
}
else
{
	// FOPEN URL is Not Supported, offer the oppertunity to do this remotely
	echo '<form method="post" action="'.ROSTER_SVNREMOTE.'">';
	echo '<input type="hidden" name="remotediag" value="true" />';
	echo '<input type="hidden" name="guildname" value="'.$roster->config['default_name'].'" />';
	echo '<input type="hidden" name="website" value="'.ROSTER_PATH .'" />';

	foreach ($files as $directory => $filedata)
	{
		foreach ($filedata as $filename => $file)
		{
			echo '<input type="hidden" name="files['.$directory.']['.$filename.'][versionDesc]" value="'.$file['local']['versionDesc'].'" />';
			echo '<input type="hidden" name="files['.$directory.']['.$filename.'][versionRev]" value="'.$file['local']['versionRev'].'" />';
			echo '<input type="hidden" name="files['.$directory.']['.$filename.'][versionDate]" value="'.$file['local']['versionDate'].'" />';
			echo '<input type="hidden" name="files['.$directory.']['.$filename.'][versionAuthor]" value="'.$file['local']['versionAuthor'].'" />';
			echo '<input type="hidden" name="files['.$directory.']['.$filename.'][versionMD5]" value="'.$file['local']['versionMD5'].'" />';
		}
	}
	echo border('sblue','start','File Version Information');
	echo '<div class="membersRowRight1"><div align="center">Cannot access Roster site for file integrity checking<br />Please press the button to perform a remote File Verion Check';
	echo '<br /><br /><input type="submit" value="Check files Remotely"></div></div>';
	echo border('sblue','end');
}
