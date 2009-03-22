<?php
/**
*
* @package acp
* @version $Id: acp_shoutbox.php 278 2008-04-13 08:42:03Z paul $
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* @todo add cron intervals to server settings? (database_gc, queue_interval, session_gc, search_gc, cache_gc, warnings_gc)
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package acp
*/
class acp_shoutbox
{
	var $u_action;
	var $new_config = array();

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang(array('acp/board', 'acp/mods/ajax_shoutbox'));
		
		require($phpbb_root_path . 'includes/functions_shoutbox.' . $phpEx);

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_shoutbox';
		add_form_key($form_key);
		
		$this->tpl_name = 'acp_shoutbox';

		/**
		*	Validation types are:
		*		string, int, bool,
		*		script_path (absolute path in url - beginning with / and no trailing slash),
		*		rpath (relative), rwpath (realtive, writable), path (relative path, but able to escape the root), wpath (writable)
		*/
		switch ($mode)
		{
		    case 'settings':
		        $this->tpl_name = 'acp_board';
				$display_vars = array(
					'title'	=> 'ACP_SHOUTBOX_SETTINGS',
					'vars'	=> array(
						'legend1'				=> 'GENERAL_SETTINGS',
						'as_prune'				=> array('lang' => 'AS_PRUNE_TIME',			'validate' => 'int',	'type' => 'text:3:10', 'explain' => true, 'append' => ' ' . $user->lang['HOURS']),
						'as_max_posts'			=> array('lang' => 'AS_MAX_POSTS',			'validate' => 'int',	'type' => 'text:3:10', 'explain' => true),
						
						'as_flood_interval'		=> array('lang' => 'AS_FLOOD_INTERVAL',		'validate' => 'int',	'type' => 'text:3:10', 'explain' => true, 'append' => ' ' . $user->lang['SECONDS']),
						
						'as_ie_nr'				=> array('lang' => 'AS_IE_NR',				'validate' => 'int',	'type' => 'text:3:10', 'explain' => true, 'append' => ' ' . $user->lang['MESSAGES']),
						'as_non_ie_nr'			=> array('lang' => 'AS_NON_IE_NR',			'validate' => 'int',	'type' => 'text:3:10', 'explain' => true, 'append' => ' ' . $user->lang['MESSAGES']),

					)
				);
			break;
			
			case 'overview':
			    $this->page_title = 'ACP_SHOUTBOX_OVERVIEW';
			    
				$action = request_var('action', '');

				if ($action)
				{
					if (!confirm_box(true))
					{
						switch ($action)
						{
							default:
								$confirm = true;
								$confirm_lang = 'CONFIRM_OPERATION';
						}

						if ($confirm)
						{
							confirm_box(false, $user->lang[$confirm_lang], build_hidden_fields(array(
								'i'			=> $id,
								'mode'		=> $mode,
								'action'	=> $action,
							)));
						}
					}
					else
					{
						switch ($action)
						{
						    case 'purge':
						        $sql = 'DELETE FROM ' . SHOUTBOX_TABLE;
						        $db->sql_query($sql);
								add_log('admin', 'LOG_PURGE_SHOUTBOX');
							break;
						}
					}
				}
				
				// Get current and latest version
				$errstr = '';
				$errno = 0;

				$info = get_remote_file('www.paulscripts.nl', '/', 'shoutbox.txt', $errstr, $errno);

				if ($info !== false)
				{
					$info = explode("\n", $info);
					$latest_version = trim($info[0]);

					$up_to_date = (version_compare(str_replace('rc', 'RC', strtolower(VERSION)), str_replace('rc', 'RC', strtolower($latest_version)), '<')) ? false : true;
					
					if (!$up_to_date)
					{
						$template->assign_vars(array(
							'S_ERROR'   => true,
							'ERROR_MSG' => sprintf($user->lang['NEW_VERSION'], VERSION, $latest_version, trim($info[1])),
						));
					}
					

				}
				else
				{
					$template->assign_vars(array(
						'S_ERROR'   => true,
						'ERROR_MSG' => sprintf($user->lang['UNABLE_CONNECT'], $errstr),
					));
				}


				
				$sql = 'SELECT COUNT(shout_id) as total FROM ' . SHOUTBOX_TABLE;
				$result = $db->sql_query($sql);
				$total_posts = $db->sql_fetchfield('total', $result);

				$template->assign_vars(array(
					'TOTAL_POSTS'		=> $total_posts,
					'AS_VERSION'        => VERSION,

					'U_ACTION'			=> append_sid($this->u_action),
					)
				);
			break;

			default:
				trigger_error('NO_MODE', E_USER_ERROR);
			break;
		}
		
		if ($mode == 'settings')
		{
			if (isset($display_vars['lang']))
			{
				$user->add_lang($display_vars['lang']);
			}

			$this->new_config = $config;
			$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
			$error = array();

			// We validate the complete config if whished
			validate_config_vars($display_vars['vars'], $cfg_array, $error);

			if ($submit && !check_form_key($form_key))
			{
				$error[] = $user->lang['FORM_INVALID'];
			}
			// Do not write values if there is an error
			if (sizeof($error))
			{
			
				$submit = false;
			}

			// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
			foreach ($display_vars['vars'] as $config_name => $null)
			{
				if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
				{
					continue;
				}

				$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

				if ($submit)
				{
					set_config($config_name, $config_value);
				}
			}

			if ($submit)
			{
				add_log('admin', 'LOG_AS_CONFIG_' . strtoupper($mode));

				trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
			}

			$this->tpl_name = 'acp_board';
			$this->page_title = $display_vars['title'];

			$template->assign_vars(array(
				'L_TITLE'			=> $user->lang[$display_vars['title']],
				'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

				'S_ERROR'			=> (sizeof($error)) ? true : false,
				'ERROR_MSG'			=> implode('<br />', $error),

				'U_ACTION'			=> $this->u_action)
			);

			// Output relevant page
			foreach ($display_vars['vars'] as $config_key => $vars)
			{
				if (!is_array($vars) && strpos($config_key, 'legend') === false)
				{
					continue;
				}

				if (strpos($config_key, 'legend') !== false)
				{
					$template->assign_block_vars('options', array(
						'S_LEGEND'		=> true,
						'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
					);

					continue;
				}

				$type = explode(':', $vars['type']);

				$l_explain = '';
				if ($vars['explain'] && isset($vars['lang_explain']))
				{
					$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
				}
				else if ($vars['explain'])
				{
					$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
				}

				$template->assign_block_vars('options', array(
					'KEY'			=> $config_key,
					'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
					'S_EXPLAIN'		=> $vars['explain'],
					'TITLE_EXPLAIN'	=> $l_explain,
					'CONTENT'		=> build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars),
					)
				);

				unset($display_vars['vars'][$config_key]);
			}
		}
	}
}

?>