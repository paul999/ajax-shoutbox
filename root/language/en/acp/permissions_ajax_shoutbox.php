<?php
/**
*
* permission info ajax shoutbox [English]
*
* @package language
* @version $Id: permissions_ajax_shoutbox.php 278 2008-04-13 08:42:03Z paul $
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
*	MODDERS PLEASE NOTE
*
*	You are able to put your permission sets into a separate file too by
*	prefixing the new file with permissions_ and putting it into the acp
*	language folder.
*
*	An example of how the file could look like:
*
*	<code>
*
*	if (empty($lang) || !is_array($lang))
*	{
*		$lang = array();
*	}
*
*	// Adding new category
*	$lang['permission_cat']['bugs'] = 'Bugs';
*
*	// Adding new permission set
*	$lang['permission_type']['bug_'] = 'Bug Permissions';
*
*	// Adding the permissions
*	$lang = array_merge($lang, array(
*		'acl_bug_view'		=> array('lang' => 'Can view bug reports', 'cat' => 'bugs'),
*		'acl_bug_post'		=> array('lang' => 'Can post bugs', 'cat' => 'post'), // Using a phpBB category here
*	));
*
*	</code>
*/

$lang['permission_cat']['shoutbox'] = 'Ajax Shoutbox';

// Teh permissions
$lang = array_merge($lang, array(
	'acl_u_as_post'				=> array('lang' => 'A user can post message to the shoutbox', 'cat' => 'shoutbox'),
	'acl_u_as_view'				=> array('lang' => 'A user can view the shoutbox', 'cat' => 'shoutbox'),
	'acl_u_as_info'				=> array('lang' => 'A user can view IPs from other users.', 'cat' => 'shoutbox'),
	'acl_u_as_delete'			=> array('lang' => 'A user can delete shouts from other users', 'cat' => 'shoutbox'),
	'acl_u_as_edit'				=> array('lang' => 'A user can edit shouts from himself', 'cat' => 'shoutbox'),
	'acl_u_as_smilies'			=> array('lang' => 'A user can post smilies', 'cat' => 'shoutbox'),
	'acl_u_as_bbcode'			=> array('lang' => 'A user can post bbcode', 'cat' => 'shoutbox'),
	'acl_u_as_mod_edit'			=> array('lang' => 'A user can edit shouts from other users', 'cat' => 'shoutbox'),
	'acl_u_as_ignore_flood'		=> array('lang' => 'A user can ignore the flood limit', 'cat' => 'shoutbox'),
	'acl_a_as_manage'			=> array('lang' => 'The user can manage the shoutbox settings', 'cat' => 'shoutbox'),
));
?>