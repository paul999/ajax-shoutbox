<?php
/** 
*
* shout [English]
*
* @package language
* @version $Id: shout.php 278 2008-04-13 08:42:03Z paul $
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
	'MISSING_DIV' 			=> 'The shoutbox div cann’t be found.',
	'NO_POST_GUEST'         => 'Guests cant post.',
	'LOADING' 				=> 'Loading',
	'POST_MESSAGE'			=> 'Post message',
	'SENDING' 				=> 'Sending message.',
	'MESSAGE_EMPTY'			=> 'Message is empty.',
	'XML_ER' 				=> 'XML error.',
	'NO_MESSAGE' 			=> 'There are no messages.',
	'NO_AJAX' 				=> 'No ajax',
	'JS_ERR' 				=> 'There has been a JavaScript error. \nError:',
	'LINE' 					=> 'Line',
	'FILE' 					=> 'File',
	'FLOOD_ERROR'	 		=> 'Flood error',
	'POSTED' 				=> 'Message posted.',
	
	'NO_QUOTE' 				=> 'Don’t use list, quote or code bbcode.',
	'SMILIES' 				=> 'Smilies', 
	'DEL_SHOUT' 			=> 'Are you sure you want to delete this shout?',
	'NO_SHOUT_ID'	 		=> 'No shout id.',
	'MSG_DEL_DONE' 			=> 'Message deleted',
    'ONLY_ONE_OPEN'         => 'You can only have 1 edit box open',
    'EDIT'                  => 'Edit',
    'CANCEL'                => 'Cancel',
    'SENDING_EDIT'          => 'sending edited post...',
    'EDIT_DONE'             => 'Message has been edited',
	
	'SHOUTBOX'				=> 'Shoutbox',
	
	'SERVER_ERR'			=> 'There was something wrong while doing a request to the server',
	
	// No permission errors
	'NO_SMILIE_PERM'    => 'You arent allowed to post smilies',
	'NO_DELETE_PERM'    => 'You arent allowed to delete messages',
	'NO_POST_PERM'		=> 'You arent allowed to post messages',
	'NO_EDIT_PERM'		=> 'You cant edit this message',
	'NO_VIEW_PERM'      => 'You arent allowed to view the shoutbox',
	'NO_ADMIN_PERM'     => 'No admin permission found',
	
	// Installation
	'MOD_INSTALLED'     => 'The MOD has been installed',
	'MOD_UPGRADED'		=> 'The MOD has been upgraded',
	'MOD_UPDATED'		=> 'The MOD has been updated',
	'NO_FOUNDER'        => 'Only founders can run this file',
	'ONLY_UPGRADE'      => 'This file is only meant for upgrades from 1.0.x',
	'ONLY_INSTALL'      => 'This file is only meant for new installs',
	'ONLY_UPDATE'       => 'This file is only meant for updates',
	'ALREADY_UPTODATE'	=> 'The database is already up to date.',
	
));
?>
