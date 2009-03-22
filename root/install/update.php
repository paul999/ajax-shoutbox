<?php
/**
*
* @package install
* @version $Id: update.php 278 2008-04-13 08:42:03Z paul $
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

if (!isset($config['as_version']))
{
	trigger_error('ONLY_UPDATE');
}
else if ($config['as_version'] == VERSION)
{
	trigger_error('ALREADY_UPTODATE');
}
$old_version = $config['as_version'];

$config_items = array(
	'as_version'		=> VERSION,
);

$config_items_dyn = array(
	'last_as_run' => 0,
);

foreach ($config_items as $name => $value)
{
	set_config($name, $value);
}

foreach ($config_items_dyn as $name => $value)
{
	set_config($name, $value, true);
}

add_log('admin', 'LOG_AS_UPDATED', $old_version, VERSION);
trigger_error('MOD_UPDATED');

?>