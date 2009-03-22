<?php
/**
*
* Module info ajax shoutbox [English]
*
* @package language
* @version $Id: info_acp_ajax_shoutbox.php 278 2008-04-13 08:42:03Z paul $
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

/**
* NOTE: Most of the language items are used in javascript
* If you want to use quotes or other chars that need escaped, be sure you escape them double
*/
$lang = array_merge($lang, array(
	'ACP_AS_MANAGEMENT'		=> 'Ajax Shoutbox',
	'ACP_SHOUTBOX'          => 'Ajax Shoutbox',
	'ACP_AS_OVERVIEW'		=> 'MOD overview',
	'ACP_AS_SETTINGS'		=> 'MOD settings',
	
	'LOG_AS_CONFIG_SETTINGS'	=> '<strong>Ajax Shoutbox settings updated.</strong>',
	'LOG_PURGE_SHOUTBOX'		=> '<strong>Shoutbox messages deleted.</strong>',
	'LOG_AS_INSTALLED'			=> '<strong>Ajax Shoutbox version %s installed.</strong>',
	'LOG_AS_UPDATED'			=> '<strong>Updated Ajax Shoutbox from %1$s to %2$s',
	'LOG_AS_UPGRADED'			=> '<strong>Upgraded Ajax Shoutbox from 1.0.x to %1$s',
	
	'LOG_AS_PRUNED'             => '<strong>Pruned Ajax Shoutbox</strong>',
	'LOG_AS_REMOVED'            => '<strong>Removed automaticlly %1$s messages</strong>',
));
?>
