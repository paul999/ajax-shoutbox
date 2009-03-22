<?php
/**
*
* @package Ajax Shoutbox
* @version $Id: functions_shoutbox.php 279 2008-04-13 08:54:14Z paul $
* @copyright (c) 2007 Paul Sohier
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

// Define the number of printed shouts.
// This is difference in IE as in other browsers ;)
// We assign it in this include as we need it in both ajax.php as in js.php.
// Do we need to check here if the config items are set? (In case of wrong update).
if (strpos(strtolower($user->browser), 'msie') === false || strpos(strtolower($user->browser), 'opera') !== false)
{
	$shout_number = (isset($config['as_non_ie_nr'])) ? $config['as_non_ie_nr'] : 20;
}
else
{
	$shout_number = (isset($config['as_ie_nr'])) ? $config['as_ie_nr'] : 5;
}
define('VERSION', '1.2.0');

/**
 * Returns cdata'd string
 *
 * @param string $txt
 * @return string
 */
function xml($contents)
{
	$contents = str_replace('&nbsp;', '', $contents);
	if ( preg_match('/\<(.*?)\>/xsi', $contents) )
	{
		$contents = preg_replace('/\<script[\s]+(.*)\>(.*)\<\/script\>/xsi', '', $contents);
	}

	if (!(strpos($contents, '>') === false) || !(strpos($contents, '<') === false) || !(strpos($contents, '&') === false))
	{
		// CDATA doesn't let you use ']]>' so fall back to WriteString
		if (!(strpos($contents, ']]>') === false))
		{
			return htmlspecialchars($contents);
		}
		else
		{
			return '<![CDATA[' . $contents . ']]>';
		}
	}
	else
	{
		return htmlspecialchars($contents);
	}
	return $contents;
}

/**
 * Prints a sql XML error.
 *
 * @param string $sql Sql query
 * @param int $line Linenumber
 * @param string $file Filename
 */
function as_sql_error($sql, $line = __LINE__, $file = __FILE__)
{
	global $db;

	$sql = xml($sql);
	$err = $db->sql_error();
	$err = xml($err['message']);
	echo "<error>$err</error>\n<sql>$sql</sql>\n</xml>";
	exit;
}

/**
 * Prints a XML error.
 * @param sring $message Error
 */
function as_error($message)
{
	$message = xml($message);
	print "<error>$message</error>\n</xml>";
	exit;
}

/**
 * Runs the cron functions
 */
function as_cron()
{
	global $db, $config;
    set_config('last_as_run', time(), true);

	if (!function_exists('add_log'))
	{
    	include ($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
	}
    
	if ($config['as_max_posts'] > 0)
	{
		$sql = 'SELECT COUNT(shout_id) as total FROM ' . SHOUTBOX_TABLE;
		$result = $db->sql_query($sql);
		
		$row = $db->sql_fetchfield('total', $result);
		$db->sql_freeresult($result);
		
        if ($row > $config['as_max_posts'])
		{
        	$sql = 'SELECT shout_id FROM ' . SHOUTBOX_TABLE . ' ORDER BY shout_time DESC';
        	$result = $db->sql_query_limit($sql, $config['as_max_posts']);
        	
        	$delete = array();
        	
        	while ($row = $db->sql_fetchrow($result))
        	{
        	    $delete[] = $row['shout_id'];
			}
			$sql = 'DELETE FROM ' . SHOUTBOX_TABLE . ' WHERE ' . $db->sql_in_set('shout_id', $delete, true);
			$db->sql_query($sql);
			
			add_log('admin', 'LOG_AS_REMOVED', $db->sql_affectedrows($result));
        }
	}
	else if ($config['as_prune'] > 0)
	{
		$time = time() - ($config['as_prune'] * 3600);

		$sql = 'DELETE FROM  ' . SHOUTBOX_TABLE . " WHERE shout_time < $time";
		$db->sql_query($sql);
		
		$deleted = $db->sql_affectedrows($result);
		
        if ($deleted > 0)
		{
			add_log('admin', 'LOG_AS_PURGED', $deleted);
        }
	}
}

/**
 * Displays the shoutbox
 */
function as_display()
{
	global $auth, $template, $user, $config;
	global $phpbb_root_path, $phpEx;
	
	// If it isnt installed we cant display it.
	if (!isset($config['as_version']))
	{
		return;
	}
	
	$user->add_lang('mods/shout');
	$template->assign_vars(array(
		'S_DISPLAY_SHOUTBOX'	=> $auth->acl_get('u_as_view') ? true : false,
		'S_CAN_VIEW_AS'			=> $auth->acl_get('u_as_view') ? true : false,
		'U_SHOUT'				=> append_sid("{$phpbb_root_path}js.$phpEx"),
		'U_SHOUT_STATIC'		=> append_sid("{$phpbb_root_path}static.js"),
	));
	// Do the shoutbox Prune thang - cron type job ...
	if ($config['last_as_run'] + $config['as_interval'] < time())
	{
		$template->assign_var('RUN_CRON_TASK', '<img src="' . append_sid($phpbb_root_path . 'cron.' . $phpEx, 'cron_type=as') . '" alt="cron" width="1" height="1" />');
	}
}
?>
