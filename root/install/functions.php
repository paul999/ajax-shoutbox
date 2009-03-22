<?php
/**
*
* @package install
* @version $Id: functions.php 278 2008-04-13 08:42:03Z paul $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* This file has been copied from the phpBB package.
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * The next functions are written by evil<3
 */
 
/**
 * Populate the module tables
 * Taken from install/install_install.php
 * Sucks a bit, because it will create the whole category as well, so only use it for installing a whole cat.
 */
function add_modules(&$module_categories, &$module_extras)
{
	global $db, $lang, $phpbb_root_path, $phpEx;

	if (!class_exists('acp_modules'))
	{
		include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
	}

	$_module = &new acp_modules();
	$module_classes = array_keys($module_categories);

	// Add categories
	foreach ($module_classes as $module_class)
	{
		$categories = array();

		// Set the module class
		$_module->module_class = $module_class;

		foreach ($module_categories[$module_class] as $cat_name => $subs)
		{
			$module_data = array(
				'module_basename'	=> '',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> 0,
				'module_class'		=> $module_class,
				'module_langname'	=> $cat_name,
				'module_mode'		=> '',
				'module_auth'		=> '',
			);

			// Add category
			$_module->update_module_data($module_data);

			$categories[$cat_name]['id'] = (int) $module_data['module_id'];
			$categories[$cat_name]['parent_id'] = 0;

			// Create sub-categories...
			if (is_array($subs))
			{
				foreach ($subs as $level2_name)
				{
					$module_data = array(
						'module_basename'	=> '',
						'module_enabled'	=> 1,
						'module_display'	=> 1,
						'parent_id'			=> (int) $categories[$cat_name]['id'],
						'module_class'		=> $module_class,
						'module_langname'	=> $level2_name,
						'module_mode'		=> '',
						'module_auth'		=> '',
					);

					$_module->update_module_data($module_data);

					$categories[$level2_name]['id'] = (int) $module_data['module_id'];
					$categories[$level2_name]['parent_id'] = (int) $categories[$cat_name]['id'];
				}
			}
		}

		// Get the modules we want to add... returned sorted by name
		$module_info = $_module->get_module_infos('', $module_class);

		foreach ($module_info as $module_basename => $fileinfo)
		{
			foreach ($fileinfo['modes'] as $module_mode => $row)
			{
				foreach ($row['cat'] as $cat_name)
				{
					if (!isset($categories[$cat_name]))
					{
						continue;
					}

					$module_data = array(
						'module_basename'	=> $module_basename,
						'module_enabled'	=> 1,
						'module_display'	=> (isset($row['display'])) ? (int) $row['display'] : 1,
						'parent_id'			=> (int) $categories[$cat_name]['id'],
						'module_class'		=> $module_class,
						'module_langname'	=> $row['title'],
						'module_mode'		=> $module_mode,
						'module_auth'		=> $row['auth'],
					);

					$_module->update_module_data($module_data);
				}
			}
		}

		// Move some of the modules around since the code above will put them in the wrong place
		if ($module_class == 'acp')
		{
			// Move main module 4 up...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'main'
					AND module_class = 'acp'
					AND module_mode = 'main'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$_module->move_module_by($row, 'move_up', 4);

			// Move permissions intro screen module 4 up...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'permissions'
					AND module_class = 'acp'
					AND module_mode = 'intro'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$_module->move_module_by($row, 'move_up', 4);

			// Move manage users screen module 5 up...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'users'
					AND module_class = 'acp'
					AND module_mode = 'overview'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$_module->move_module_by($row, 'move_up', 5);
		}

		if ($module_class == 'ucp')
		{
			// Move attachment module 4 down...
			$sql = 'SELECT *
				FROM ' . MODULES_TABLE . "
				WHERE module_basename = 'attachments'
					AND module_class = 'ucp'
					AND module_mode = 'attachments'";
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);

			$_module->move_module_by($row, 'move_down', 4);
		}

		// And now for the special ones
		// (these are modules which appear in multiple categories and thus get added manually to some for more control)
		if (isset($module_extras[$module_class]))
		{
			foreach ($module_extras[$module_class] as $cat_name => $mods)
			{
				$sql = 'SELECT module_id, left_id, right_id
					FROM ' . MODULES_TABLE . "
					WHERE module_langname = '" . $db->sql_escape($cat_name) . "'
						AND module_class = '" . $db->sql_escape($module_class) . "'";
				$result = $db->sql_query_limit($sql, 1);
				$row2 = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				foreach ($mods as $mod_name)
				{
					$sql = 'SELECT *
						FROM ' . MODULES_TABLE . "
						WHERE module_langname = '" . $db->sql_escape($mod_name) . "'
							AND module_class = '" . $db->sql_escape($module_class) . "'
							AND module_basename <> ''";
					$result = $db->sql_query_limit($sql, 1);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					$module_data = array(
						'module_basename'	=> $row['module_basename'],
						'module_enabled'	=> (int) $row['module_enabled'],
						'module_display'	=> (int) $row['module_display'],
						'parent_id'			=> (int) $row2['module_id'],
						'module_class'		=> $row['module_class'],
						'module_langname'	=> $row['module_langname'],
						'module_mode'		=> $row['module_mode'],
						'module_auth'		=> $row['module_auth'],
					);

					$_module->update_module_data($module_data);
				}
			}
		}

		$_module->remove_cache_file();
	}
}

/**
 * Adds user-defined module to ACP (or MCP, etc). Useful for install scripts
 * John Wells, based on original phpBB code in acp_modules.
 *
 * @param array $module_data -- array containing module_basename, module_mode, module_auth, module_enabled, module_display, parent_id, module_langname and module_class
 * @param array $error Store all errors in there
 * @return mixed module id
 */
function add_module(&$module_data, &$error)
{
	global $phpbb_root_path, $phpEx;

	// better than include_once
	if (!class_exists('acp_modules'))
	{
		include($phpbb_root_path . 'includes/acp/acp_modules.' . $phpEx);
	}

	$_module = &new acp_modules();
	$_module->module_class = $module_data['module_class'];

	$module_id = module_exists($module_data['module_langname'], $module_data['parent_id']);

	if ($module_id)
	{
		$module_data['module_id'] = $module_id;
	}

	// Adjust auth row if not category
	if ($module_data['module_basename'] && $module_data['module_mode'])
	{
		$fileinfo = $_module->get_module_infos($module_data['module_basename']);
		$module_data['module_auth'] = $fileinfo[$module_data['module_basename']]['modes'][$module_data['module_mode']]['auth'];
	}

	$error = $_module->update_module_data($module_data, true);

	$_module->remove_cache_file();

	if (sizeof($error))
	{
		return false;
	}

	return $module_data['module_id'];
}

/**
 * Determines if a module already exists, and returns the module ID if it does.
 * More than one module with the same name and parent could exist, but this function just returns the first one it finds.
 * The alternatives are to delete duplicates, or throw up an error, neither of which is really better behaviour.
 * John Wells
 *
 * @param string $module_name -- module name (or language key)
 * @param integer $parent -- the id of the parent entity
 * @return mixed module_exists
 */
function module_exists($module_name, $parent = 0)
{
	global $db;

	$sql = 'SELECT module_id FROM ' . MODULES_TABLE . '
		WHERE parent_id = ' . intval($parent) . '
			AND module_langname = \'' . $db->sql_escape($module_name) . '\'';
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	// there could be a duplicate module, but screw it
	if (!$row || empty($row['module_id']))
	{
		return false;
	}

	return $row['module_id'];
}

/**
 * Install a single module into existing categories
 * and create categories if they don't exist
 *
 * @param string $module_class The module class, like ucp/mcp/ucp
 * @param string $module_name The modules filename minus extension and class_
 * @param array $error Passed by reference array for errors
 * @param mixed $main_category Only used for ACP if there's a category above the one stored in the file
 * @return mixed module_ids Array of module ids added
 */
function install_module($module_class, $module_name, &$error, $main_category = false)
{
	global $phpbb_root_path, $phpEx;

	$class_name = $module_name . '_info';
	$module_filename = "{$phpbb_root_path}includes/$module_class/info/{$module_name}.$phpEx";

	if (!class_exists($class_name))
	{
		include($module_filename);
	}

	$module_info = call_user_func(array($class_name, 'module'));

	$module_ids = array();

	if ($main_category)
	{
		if (!$module_main_cat = module_exists($main_category))
		{
			$module_data = array(
				'module_basename'	=> '',
				'module_mode'		=> '',
				'module_auth'		=> '',
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> 0,
				'module_langname'	=> $main_category,
				'module_class'		=> $module_class,
			);

			$module_main_cat = add_module($module_data, $error);
		}
	}

	foreach ($module_info['modes'] as $mode => $mode_data)
	{
		foreach ($mode_data['cat'] as $category)
		{
			if (!$module_cat = module_exists($category))
			{
				$module_data = array(
					'module_basename'	=> '',
					'module_mode'		=> '',
					'module_auth'		=> '',
					'module_enabled'	=> 1,
					'module_display'	=> 1,
					'parent_id'			=> ($main_category) ? $module_main_cat : 0,
					'module_langname'	=> $category,
					'module_class'		=> $module_class,
				);

				$module_cat = add_module($module_data, $error);
			}

			$module_data = array(
				'module_basename'	=> str_replace("{$module_class}_", '', $module_info['filename']),
				'module_mode'		=> $mode,
				'module_auth'		=> $mode_data['auth'],
				'module_enabled'	=> 1,
				'module_display'	=> 1,
				'parent_id'			=> $module_cat,
				'module_langname'	=> $mode_data['title'],
				'module_class'		=> $module_class,
			);

			$module_ids[] = add_module($module_data, $error);
		}
	}

	return $module_ids;
}
?>