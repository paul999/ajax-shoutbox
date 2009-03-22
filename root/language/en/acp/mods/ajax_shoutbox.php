<?php
/**
*
* Module info ajax shoutbox [English]
*
* @package language
* @version $Id: ajax_shoutbox.php 278 2008-04-13 08:42:03Z paul $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACP_SHOUTBOX_SETTINGS'				=> 'Ajax shoutbox settings',
	'ACP_SHOUTBOX_SETTINGS_EXPLAIN'     => 'You will find some basic settings of the ajax shoutbox.',
	'ACP_SHOUTBOX_OVERVIEW'             => 'Ajax shoutbox overview',

	// Overview
	'AS_OVERVIEW'			=> 'MOD overview',
	'AS_OVERVIEW_EXPLAIN'	=> 'Here below you will find some stats about the Ajax Shoutbox.<br />
	If you find a bug, or want to request a feature, please visit our <a href="http://www.paulsohier.nl/ajax">trac</a>.<br />
	Before submitting, please check first if the bug or feature isnt already reported. <br />
	A lot information about the shoutbox, the development status and more can also be found at our trac.<br />
	The permissions for the shoutbox can be found at the permission tab at the top, and then user or groups permissions.',

	
	'AS_STATS'      => 'Shoutbox statistics',
	'NUMBER_SHOUTS' => 'Total number of shouts',
	'AS_VERSION'    => 'Shoutbox version',
	'AS_OPTIONS'    => 'Shoutbox options',
	'PURGE_AS'      => 'Delete all messages',
	
	'UNABLE_CONNECT'    => 'I was unable to connect to the version check server, I got this error: %s',
	'NEW_VERSION'       => 'Your version of ajax shoutbox is not up to date. Your version is %1$s, the newest version is %2$s. Read <a href="%3$s">this</a> for more information',
	
	
	// Configuration
	'AS_PRUNE_TIME'				=> 'Prune time',
	'AS_PRUNE_TIME_EXPLAIN'		=> 'The time when the messages are pruned automaticcly. When maximum posts setting is enabled, that will overide this setting. Set this setting to 0 to disable',
	'AS_MAX_POSTS'				=> 'Maximum number of shouts',
	'AS_MAX_POSTS_EXPLAIN'		=> 'Maximum numbers of shouts. Set 0 to disable. If this setting if enabled, it will <strong>overide</strong> the prune setting!',
	
	'AS_FLOOD_INTERVAL'         => 'Flood interval',
	'AS_FLOOD_INTERVAL_EXPLAIN' => 'The time minimum time between 2 posts for users. Set 0 to disable.',
	
	'AS_IE_NR'				=> 'Number of messages',
	'AS_IE_NR_EXPLAIN'		=> 'The number of messages in internet explorer. Due to some IE bugs, you need to make it not to high.',
	'AS_NON_IE_NR'			=> 'Number of messages',
	'AS_NON_IE_NR_EXPLAIN'	=> 'The number of messages in other browser as IE.',
));
?>