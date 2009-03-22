<?php
/**
*
* @package install
* @version $Id: upgrade.php 278 2008-04-13 08:42:03Z paul $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* This file has been copied from the phpBB package.
*
*/

/**#@+
* @ignore
*/
define('IN_PHPBB', true);
define('IN_INSTALL', true);
/**#@-*/

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include ($phpbb_root_path . 'common.' . $phpEx);
include ($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
include ($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
include ($phpbb_root_path . 'includes/functions_shoutbox.' . $phpEx);
include ($phpbb_root_path . 'install/functions.' . $phpEx);

// Start session management
$user->session_begin(false);
$auth->acl($user->data);
$user->setup('mods/shout');

if ($user->data['user_type'] != USER_FOUNDER)
{
	trigger_error('NO_FOUNDER');
}

if (isset($config['as_version']))
{
	trigger_error('ONLY_UPGRADE');
}

$config_items = array(
	'as_interval'		=> 3600,
	'as_prune'			=> (24 * 14),
	'as_max_posts'		=> 0,
	'as_flood_interval' => 15,
	'as_version'		=> VERSION,
	'as_ie_nr'			=> 5,
	'as_non_ie_nr'		=> 20,
);

$config_items_dyn = array(
	'last_as_run' => 0,

);

$permissions = array(
	'global' => array(
		'u_as_post',
		'u_as_view',
		'u_as_info',
		'u_as_delete',
		'u_as_edit',
		'u_as_smilies',
		'u_as_bbcode',
		'u_as_mod_edit',
		'u_as_ignore_flood',
		'a_as_manage',
	)
);

foreach ($config_items as $name => $value)
{
	set_config($name, $value);
}

foreach ($config_items_dyn as $name => $value)
{
	set_config($name, $value, true);
}

$acl = new auth_admin();
$acl->acl_add_option($permissions);

$error = array();
install_module('acp', 'acp_shoutbox', &$error, 'ACP_CAT_DOT_MODS');

add_log('admin', 'LOG_AS_UPGRADED', VERSION);
trigger_error('MOD_UPGRADED');

?>