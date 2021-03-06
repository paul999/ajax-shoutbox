<?php
/**
*
* @package install
* @version $Id: index.php 278 2008-04-13 08:42:03Z paul $
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
	trigger_error('ONLY_INSTALL');
}

$dbms = $db->sql_layer;
$table = '';

generate_sql($table, $dbms);

if (empty($table))
{
	// Should never happen.
	trigger_error('This error should not happen');
}

$config_items = array(
	'as_interval'		=> 3600,
	'as_prune'			=> (24 * 14),
	'as_max_posts'		=> 0,
	'as_flood_interval' => 15,
	'as_version'        => VERSION,
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



if (!function_exists('get_available_dbms'))
{
	global $phpbb_root_path, $phpEx;
	include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
}

$available_dbms = get_available_dbms();

if ($dbms == 'mysql4' || $dbms == 'mysql')
{

	if (version_compare($db->mysql_version, '4.1.3', '>='))
	{
		$available_dbms['mysql']['SCHEMA'] .= '_41';
		$dbms = 'mysql';
	}
	else
	{
		$available_dbms[$dbms]['SCHEMA'] .= '_40';
	}
}

$remove_remarks = $available_dbms[$dbms]['COMMENTS'];
$delimiter = $available_dbms[$dbms]['DELIM'];

$sql_query = preg_replace('#phpbb_#i', $table_prefix, $table);

$remove_remarks($sql_query);

$sql_query = split_sql_file($sql_query, $delimiter);

foreach ($sql_query as $sql)
{
	$db->sql_query($sql);
}
unset($sql_query);

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
install_module('acp', 'acp_shoutbox', $error, 'ACP_CAT_DOT_MODS');

add_log('admin', 'LOG_AS_INSTALLED', VERSION);
trigger_error('MOD_INSTALLED');

function generate_sql(&$lineg, $dbms)
{
	global $phpbb_root_path, $phpEx;
	
	$dbms_type_map = array(
		'mysql_41'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'mediumint(8) UNSIGNED',
			'UINT:'		=> 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'smallint(4) UNSIGNED',
			'BOOL'		=> 'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'text',
			'XSTEXT_UNI'=> 'varchar(100)',
			'STEXT'		=> 'text',
			'STEXT_UNI'	=> 'varchar(255)',
			'TEXT'		=> 'text',
			'TEXT_UNI'	=> 'text',
			'MTEXT'		=> 'mediumtext',
			'MTEXT_UNI'	=> 'mediumtext',
			'TIMESTAMP'	=> 'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'varbinary(255)',
		),

		'mysql_40'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'mediumint(8) UNSIGNED',
			'UINT:'		=> 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'smallint(4) UNSIGNED',
			'BOOL'		=> 'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varbinary(255)',
			'VCHAR:'	=> 'varbinary(%d)',
			'CHAR:'		=> 'binary(%d)',
			'XSTEXT'	=> 'blob',
			'XSTEXT_UNI'=> 'blob',
			'STEXT'		=> 'blob',
			'STEXT_UNI'	=> 'blob',
			'TEXT'		=> 'blob',
			'TEXT_UNI'	=> 'blob',
			'MTEXT'		=> 'mediumblob',
			'MTEXT_UNI'	=> 'mediumblob',
			'TIMESTAMP'	=> 'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'blob',
			'VCHAR_UNI:'=> array('varbinary(%d)', 'limit' => array('mult', 3, 255, 'blob')),
			'VCHAR_CI'	=> 'blob',
			'VARBINARY'	=> 'varbinary(255)',
		),

		'firebird'	=> array(
			'INT:'		=> 'INTEGER',
			'BINT'		=> 'DOUBLE PRECISION',
			'UINT'		=> 'INTEGER',
			'UINT:'		=> 'INTEGER',
			'TINT:'		=> 'INTEGER',
			'USINT'		=> 'INTEGER',
			'BOOL'		=> 'INTEGER',
			'VCHAR'		=> 'VARCHAR(255) CHARACTER SET NONE',
			'VCHAR:'	=> 'VARCHAR(%d) CHARACTER SET NONE',
			'CHAR:'		=> 'CHAR(%d) CHARACTER SET NONE',
			'XSTEXT'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'STEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'TEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'MTEXT'		=> 'BLOB SUB_TYPE TEXT CHARACTER SET NONE',
			'XSTEXT_UNI'=> 'VARCHAR(100) CHARACTER SET UTF8',
			'STEXT_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'TEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
			'MTEXT_UNI'	=> 'BLOB SUB_TYPE TEXT CHARACTER SET UTF8',
			'TIMESTAMP'	=> 'INTEGER',
			'DECIMAL'	=> 'DOUBLE PRECISION',
			'DECIMAL:'	=> 'DOUBLE PRECISION',
			'PDECIMAL'	=> 'DOUBLE PRECISION',
			'PDECIMAL:'	=> 'DOUBLE PRECISION',
			'VCHAR_UNI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'VCHAR_UNI:'=> 'VARCHAR(%d) CHARACTER SET UTF8',
			'VCHAR_CI'	=> 'VARCHAR(255) CHARACTER SET UTF8',
			'VARBINARY'	=> 'CHAR(255) CHARACTER SET NONE',
		),

		'mssql'		=> array(
			'INT:'		=> '[int]',
			'BINT'		=> '[float]',
			'UINT'		=> '[int]',
			'UINT:'		=> '[int]',
			'TINT:'		=> '[int]',
			'USINT'		=> '[int]',
			'BOOL'		=> '[int]',
			'VCHAR'		=> '[varchar] (255)',
			'VCHAR:'	=> '[varchar] (%d)',
			'CHAR:'		=> '[char] (%d)',
			'XSTEXT'	=> '[varchar] (1000)',
			'STEXT'		=> '[varchar] (3000)',
			'TEXT'		=> '[varchar] (8000)',
			'MTEXT'		=> '[text]',
			'XSTEXT_UNI'=> '[varchar] (100)',
			'STEXT_UNI'	=> '[varchar] (255)',
			'TEXT_UNI'	=> '[varchar] (4000)',
			'MTEXT_UNI'	=> '[text]',
			'TIMESTAMP'	=> '[int]',
			'DECIMAL'	=> '[float]',
			'DECIMAL:'	=> '[float]',
			'PDECIMAL'	=> '[float]',
			'PDECIMAL:'	=> '[float]',
			'VCHAR_UNI'	=> '[varchar] (255)',
			'VCHAR_UNI:'=> '[varchar] (%d)',
			'VCHAR_CI'	=> '[varchar] (255)',
			'VARBINARY'	=> '[varchar] (255)',
		),

		'oracle'	=> array(
			'INT:'		=> 'number(%d)',
			'BINT'		=> 'number(20)',
			'UINT'		=> 'number(8)',
			'UINT:'		=> 'number(%d)',
			'TINT:'		=> 'number(%d)',
			'USINT'		=> 'number(4)',
			'BOOL'		=> 'number(1)',
			'VCHAR'		=> 'varchar2(255)',
			'VCHAR:'	=> 'varchar2(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'varchar2(1000)',
			'STEXT'		=> 'varchar2(3000)',
			'TEXT'		=> 'clob',
			'MTEXT'		=> 'clob',
			'XSTEXT_UNI'=> 'varchar2(300)',
			'STEXT_UNI'	=> 'varchar2(765)',
			'TEXT_UNI'	=> 'clob',
			'MTEXT_UNI'	=> 'clob',
			'TIMESTAMP'	=> 'number(11)',
			'DECIMAL'	=> 'number(5, 2)',
			'DECIMAL:'	=> 'number(%d, 2)',
			'PDECIMAL'	=> 'number(6, 3)',
			'PDECIMAL:'	=> 'number(%d, 3)',
			'VCHAR_UNI'	=> 'varchar2(765)',
			'VCHAR_UNI:'=> array('varchar2(%d)', 'limit' => array('mult', 3, 765, 'clob')),
			'VCHAR_CI'	=> 'varchar2(255)',
			'VARBINARY'	=> 'raw(255)',
		),

		'sqlite'	=> array(
			'INT:'		=> 'int(%d)',
			'BINT'		=> 'bigint(20)',
			'UINT'		=> 'INTEGER UNSIGNED', //'mediumint(8) UNSIGNED',
			'UINT:'		=> 'INTEGER UNSIGNED', // 'int(%d) UNSIGNED',
			'TINT:'		=> 'tinyint(%d)',
			'USINT'		=> 'INTEGER UNSIGNED', //'mediumint(4) UNSIGNED',
			'BOOL'		=> 'INTEGER UNSIGNED', //'tinyint(1) UNSIGNED',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'text(65535)',
			'STEXT'		=> 'text(65535)',
			'TEXT'		=> 'text(65535)',
			'MTEXT'		=> 'mediumtext(16777215)',
			'XSTEXT_UNI'=> 'text(65535)',
			'STEXT_UNI'	=> 'text(65535)',
			'TEXT_UNI'	=> 'text(65535)',
			'MTEXT_UNI'	=> 'mediumtext(16777215)',
			'TIMESTAMP'	=> 'INTEGER UNSIGNED', //'int(11) UNSIGNED',
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar(255)',
			'VARBINARY'	=> 'blob',
		),

		'postgres'	=> array(
			'INT:'		=> 'INT4',
			'BINT'		=> 'INT8',
			'UINT'		=> 'INT4', // unsigned
			'UINT:'		=> 'INT4', // unsigned
			'USINT'		=> 'INT2', // unsigned
			'BOOL'		=> 'INT2', // unsigned
			'TINT:'		=> 'INT2',
			'VCHAR'		=> 'varchar(255)',
			'VCHAR:'	=> 'varchar(%d)',
			'CHAR:'		=> 'char(%d)',
			'XSTEXT'	=> 'varchar(1000)',
			'STEXT'		=> 'varchar(3000)',
			'TEXT'		=> 'varchar(8000)',
			'MTEXT'		=> 'TEXT',
			'XSTEXT_UNI'=> 'varchar(100)',
			'STEXT_UNI'	=> 'varchar(255)',
			'TEXT_UNI'	=> 'varchar(4000)',
			'MTEXT_UNI'	=> 'TEXT',
			'TIMESTAMP'	=> 'INT4', // unsigned
			'DECIMAL'	=> 'decimal(5,2)',
			'DECIMAL:'	=> 'decimal(%d,2)',
			'PDECIMAL'	=> 'decimal(6,3)',
			'PDECIMAL:'	=> 'decimal(%d,3)',
			'VCHAR_UNI'	=> 'varchar(255)',
			'VCHAR_UNI:'=> 'varchar(%d)',
			'VCHAR_CI'	=> 'varchar_ci',
			'VARBINARY'	=> 'bytea',
		),
	);
	
	// A list of types being unsigned for better reference in some db's
	$unsigned_types = array('UINT', 'UINT:', 'USINT', 'BOOL', 'TIMESTAMP');
	$supported_dbms = array('firebird', 'mssql', 'mysql_40', 'mysql_41', 'oracle', 'postgres', 'sqlite');
	
	$schema_data = get_schema();

	if ($dbms == 'mysqli' || $dbms == 'mysql4')
	{
	    $dbms = 'mysql_41';
	}
	else if ($dbms == 'mysql')
	{
		$dbms = 'mysql_40';
	}

	if (in_array($dbms, $supported_dbms))
	{
		$lineg = $line = '';

		// Write Header
		switch ($dbms)
		{
			case 'mysql_40':
			case 'mysql_41':
				$line = "#\n# \$I" . "d: $\n#\n\n";
			break;

			case 'firebird':
				$line = "#\n# \$I" . "d: $\n#\n\n";
				$line .= custom_data('firebird') . "\n";
			break;

			case 'sqlite':
				$line = "#\n# \$I" . "d: $\n#\n\n";
				$line .= "BEGIN TRANSACTION;\n\n";
			break;

			case 'mssql':
				$line = "/*\n\n \$I" . "d: $\n\n*/\n\n";
				$line .= "BEGIN TRANSACTION\nGO\n\n";
			break;

			case 'oracle':
				$line = "/*\n\n \$I" . "d: $\n\n*/\n\n";
				$line .= custom_data('oracle') . "\n";
			break;

			case 'postgres':
				$line = "/*\n\n \$I" . "d: $\n\n*/\n\n";
				$line .= "BEGIN;\n\n";
				$line .= custom_data('postgres') . "\n";
			break;
		}

		$lineg .= $line;

		foreach ($schema_data as $table_name => $table_data)
		{
			// Write comment about table
			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
				case 'firebird':
				case 'sqlite':
					$lineg .= "# Table: '{$table_name}'\n";
				break;

				case 'mssql':
				case 'oracle':
				case 'postgres':
					$lineg .= "/*\n\tTable: '{$table_name}'\n*/\n";
				break;
			}

			// Create Table statement
			$generator = $textimage = false;
			$line = '';

			switch ($dbms)
			{
				case 'mysql_40':
				case 'mysql_41':
				case 'firebird':
				case 'oracle':
				case 'sqlite':
				case 'postgres':
					$line = "CREATE TABLE {$table_name} (\n";
				break;

				case 'mssql':
					$line = "CREATE TABLE [{$table_name}] (\n";
				break;
			}

			// Table specific so we don't get overlap
			$modded_array = array();

			// Write columns one by one...
			foreach ($table_data['COLUMNS'] as $column_name => $column_data)
			{
				// Get type
				if (strpos($column_data[0], ':') !== false)
				{
					list($orig_column_type, $column_length) = explode(':', $column_data[0]);
					if (!is_array($dbms_type_map[$dbms][$orig_column_type . ':']))
					{
						$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'], $column_length);
					}
					else
					{
						if (isset($dbms_type_map[$dbms][$orig_column_type . ':']['rule']))
						{
							switch ($dbms_type_map[$dbms][$orig_column_type . ':']['rule'][0])
							{
								case 'div':
									$column_length /= $dbms_type_map[$dbms][$orig_column_type . ':']['rule'][1];
									$column_length = ceil($column_length);
									$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
								break;
							}
						}

						if (isset($dbms_type_map[$dbms][$orig_column_type . ':']['limit']))
						{
							switch ($dbms_type_map[$dbms][$orig_column_type . ':']['limit'][0])
							{
								case 'mult':
									$column_length *= $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][1];
									if ($column_length > $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][2])
									{
										$column_type = $dbms_type_map[$dbms][$orig_column_type . ':']['limit'][3];
										$modded_array[$column_name] = $column_type;
									}
									else
									{
										$column_type = sprintf($dbms_type_map[$dbms][$orig_column_type . ':'][0], $column_length);
									}
								break;
							}
						}
					}
					$orig_column_type .= ':';
				}
				else
				{
					$orig_column_type = $column_data[0];
					$column_type = $dbms_type_map[$dbms][$column_data[0]];
					if ($column_type == 'text' || $column_type == 'blob')
					{
						$modded_array[$column_name] = $column_type;
					}
				}

				// Adjust default value if db-dependant specified
				if (is_array($column_data[1]))
				{
					$column_data[1] = (isset($column_data[1][$dbms])) ? $column_data[1][$dbms] : $column_data[1]['default'];
				}

				switch ($dbms)
				{
					case 'mysql_40':
					case 'mysql_41':
						$line .= "\t{$column_name} {$column_type} ";

						// For hexadecimal values do not use single quotes
						if (!is_null($column_data[1]) && substr($column_type, -4) !== 'text' && substr($column_type, -4) !== 'blob')
						{
							$line .= (strpos($column_data[1], '0x') === 0) ? "DEFAULT {$column_data[1]} " : "DEFAULT '{$column_data[1]}' ";
						}
						$line .= 'NOT NULL';

						if (isset($column_data[2]))
						{
							if ($column_data[2] == 'auto_increment')
							{
								$line .= ' auto_increment';
							}
							else if ($dbms === 'mysql_41' && $column_data[2] == 'true_sort')
							{
								$line .= ' COLLATE utf8_unicode_ci';
							}
						}

						$line .= ",\n";
					break;

					case 'sqlite':
						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$line .= "\t{$column_name} INTEGER PRIMARY KEY ";
							$generator = $column_name;
						}
						else
						{
							$line .= "\t{$column_name} {$column_type} ";
						}

						$line .= 'NOT NULL ';
						$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}'" : '';
						$line .= ",\n";
					break;

					case 'firebird':
						$line .= "\t{$column_name} {$column_type} ";

						if (!is_null($column_data[1]))
						{
							$line .= 'DEFAULT ' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ' ';
						}

						$line .= 'NOT NULL';

						// This is a UNICODE column and thus should be given it's fair share
						if (preg_match('/^X?STEXT_UNI|VCHAR_(CI|UNI:?)/', $column_data[0]))
						{
							$line .= ' COLLATE UNICODE';
						}

						$line .= ",\n";

						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$generator = $column_name;
						}
					break;

					case 'mssql':
						if ($column_type == '[text]')
						{
							$textimage = true;
						}

						$line .= "\t[{$column_name}] {$column_type} ";

						if (!is_null($column_data[1]))
						{
							// For hexadecimal values do not use single quotes
							if (strpos($column_data[1], '0x') === 0)
							{
								$line .= 'DEFAULT (' . $column_data[1] . ') ';
							}
							else
							{
								$line .= 'DEFAULT (' . ((is_numeric($column_data[1])) ? $column_data[1] : "'{$column_data[1]}'") . ') ';
							}
						}

						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$line .= 'IDENTITY (1, 1) ';
						}

						$line .= 'NOT NULL';
						$line .= " ,\n";
					break;

					case 'oracle':
						$line .= "\t{$column_name} {$column_type} ";
						$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';

						// In Oracle empty strings ('') are treated as NULL.
						// Therefore in oracle we allow NULL's for all DEFAULT '' entries
						$line .= ($column_data[1] === '') ? ",\n" : "NOT NULL,\n";

						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$generator = $column_name;
						}
					break;

					case 'postgres':
						$line .= "\t{$column_name} {$column_type} ";

						if (isset($column_data[2]) && $column_data[2] == 'auto_increment')
						{
							$line .= "DEFAULT nextval('{$table_name}_seq'),\n";

							// Make sure the sequence will be created before creating the table
							$line = "CREATE SEQUENCE {$table_name}_seq;\n\n" . $line;
						}
						else
						{
							$line .= (!is_null($column_data[1])) ? "DEFAULT '{$column_data[1]}' " : '';
							$line .= "NOT NULL";

							// Unsigned? Then add a CHECK contraint
							if (in_array($orig_column_type, $unsigned_types))
							{
								$line .= " CHECK ({$column_name} >= 0)";
							}

							$line .= ",\n";
						}
					break;
				}
			}

			switch ($dbms)
			{
				case 'firebird':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n);;\n\n";
				break;

				case 'mssql':
					$line = substr($line, 0, -2);
					$line .= "\n) ON [PRIMARY]" . (($textimage) ? ' TEXTIMAGE_ON [PRIMARY]' : '') . "\n";
					$line .= "GO\n\n";
				break;
			}

			// Write primary key
			if (isset($table_data['PRIMARY_KEY']))
			{
				if (!is_array($table_data['PRIMARY_KEY']))
				{
					$table_data['PRIMARY_KEY'] = array($table_data['PRIMARY_KEY']);
				}

				switch ($dbms)
				{
					case 'mysql_40':
					case 'mysql_41':
					case 'postgres':
						$line .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
					break;

					case 'firebird':
						$line .= "ALTER TABLE {$table_name} ADD PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . ");;\n\n";
					break;

					case 'sqlite':
						if ($generator === false || !in_array($generator, $table_data['PRIMARY_KEY']))
						{
							$line .= "\tPRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
						}
					break;

					case 'mssql':
						$line .= "ALTER TABLE [{$table_name}] WITH NOCHECK ADD \n";
						$line .= "\tCONSTRAINT [PK_{$table_name}] PRIMARY KEY  CLUSTERED \n";
						$line .= "\t(\n";
						$line .= "\t\t[" . implode("],\n\t\t[", $table_data['PRIMARY_KEY']) . "]\n";
						$line .= "\t)  ON [PRIMARY] \n";
						$line .= "GO\n\n";
					break;

					case 'oracle':
						$line .= "\tCONSTRAINT pk_{$table_name} PRIMARY KEY (" . implode(', ', $table_data['PRIMARY_KEY']) . "),\n";
					break;
				}
			}

			switch ($dbms)
			{
				case 'oracle':
					// UNIQUE contrains to be added?
					if (isset($table_data['KEYS']))
					{
						foreach ($table_data['KEYS'] as $key_name => $key_data)
						{
							if (!is_array($key_data[1]))
							{
								$key_data[1] = array($key_data[1]);
							}

							if ($key_data[0] == 'UNIQUE')
							{
								$line .= "\tCONSTRAINT u_phpbb_{$key_name} UNIQUE (" . implode(', ', $key_data[1]) . "),\n";
							}
						}
					}

					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n)\n/\n\n";
				break;

				case 'postgres':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n);\n\n";
				break;

				case 'sqlite':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n);\n\n";
				break;
			}

			// Write Keys
			if (isset($table_data['KEYS']))
			{
				foreach ($table_data['KEYS'] as $key_name => $key_data)
				{
					if (!is_array($key_data[1]))
					{
						$key_data[1] = array($key_data[1]);
					}

					switch ($dbms)
					{
						case 'mysql_40':
						case 'mysql_41':
							$line .= ($key_data[0] == 'INDEX') ? "\tKEY" : '';
							$line .= ($key_data[0] == 'UNIQUE') ? "\tUNIQUE" : '';
							foreach ($key_data[1] as $key => $col_name)
							{
								if (isset($modded_array[$col_name]))
								{
									switch ($modded_array[$col_name])
									{
										case 'text':
										case 'blob':
											$key_data[1][$key] = $col_name . '(255)';
										break;
									}
								}
							}
							$line .= ' ' . $key_name . ' (' . implode(', ', $key_data[1]) . "),\n";
						break;

						case 'firebird':
							$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
							$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

							$line .= ' ' . $table_name . '_' . $key_name . ' ON ' . $table_name . '(' . implode(', ', $key_data[1]) . ");;\n";
						break;

						case 'mssql':
							$line .= ($key_data[0] == 'INDEX') ? 'CREATE  INDEX' : '';
							$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE  UNIQUE  INDEX' : '';
							$line .= " [{$key_name}] ON [{$table_name}]([" . implode('], [', $key_data[1]) . "]) ON [PRIMARY]\n";
							$line .= "GO\n\n";
						break;

						case 'oracle':
							if ($key_data[0] == 'UNIQUE')
							{
								continue;
							}

							$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';

							$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ")\n";
							$line .= "/\n";
						break;

						case 'sqlite':
							$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
							$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

							$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
						break;

						case 'postgres':
							$line .= ($key_data[0] == 'INDEX') ? 'CREATE INDEX' : '';
							$line .= ($key_data[0] == 'UNIQUE') ? 'CREATE UNIQUE INDEX' : '';

							$line .= " {$table_name}_{$key_name} ON {$table_name} (" . implode(', ', $key_data[1]) . ");\n";
						break;
					}
				}
			}

			switch ($dbms)
			{
				case 'mysql_40':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n);\n\n";
				break;

				case 'mysql_41':
					// Remove last line delimiter...
					$line = substr($line, 0, -2);
					$line .= "\n) CHARACTER SET utf8 COLLATE utf8_bin;\n\n";
				break;

				// Create Generator
				case 'firebird':
					if ($generator !== false)
					{
						$line .= "\nCREATE GENERATOR {$table_name}_gen;;\n";
						$line .= 'SET GENERATOR ' . $table_name . "_gen TO 0;;\n\n";

						$line .= 'CREATE TRIGGER t_' . $table_name . ' FOR ' . $table_name . "\n";
						$line .= "BEFORE INSERT\nAS\nBEGIN\n";
						$line .= "\tNEW.{$generator} = GEN_ID({$table_name}_gen, 1);\nEND;;\n\n";
					}
				break;

				case 'oracle':
					if ($generator !== false)
					{
						$line .= "\nCREATE SEQUENCE {$table_name}_seq\n/\n\n";

						$line .= "CREATE OR REPLACE TRIGGER t_{$table_name}\n";
						$line .= "BEFORE INSERT ON {$table_name}\n";
						$line .= "FOR EACH ROW WHEN (\n";
						$line .= "\tnew.{$generator} IS NULL OR new.{$generator} = 0\n";
						$line .= ")\nBEGIN\n";
						$line .= "\tSELECT {$table_name}_seq.nextval\n";
						$line .= "\tINTO :new.{$generator}\n";
						$line .= "\tFROM dual;\nEND;\n/\n\n";
					}
				break;
			}

			$lineg .= $line . "\n";
		}

		$line = '';

		// Write custom function at the end for some db's
		switch ($dbms)
		{
			case 'mssql':
				$line = "\nCOMMIT\nGO\n\n";
			break;

			case 'sqlite':
				$line = "\nCOMMIT;";
			break;

			case 'postgres':
				$line = "\nCOMMIT;";
			break;
		}

		$lineg .= $line;
		return;
	}
	else
	{
		trigger_error('This error should also never happen');
	}
}

/**
* Define the basic structure
* The format:
*		array('{TABLE_NAME}' => {TABLE_DATA})
*		{TABLE_DATA}:
*			COLUMNS = array({column_name} = array({column_type}, {default}, {auto_increment}))
*			PRIMARY_KEY = {column_name(s)}
*			KEYS = array({key_name} = array({key_type}, {column_name(s)})),
*
*	Column Types:
*	INT:x		=> SIGNED int(x)
*	BINT		=> BIGINT
*	UINT		=> mediumint(8) UNSIGNED
*	UINT:x		=> int(x) UNSIGNED
*	TINT:x		=> tinyint(x)
*	USINT		=> smallint(4) UNSIGNED (for _order columns)
*	BOOL		=> tinyint(1) UNSIGNED
*	VCHAR		=> varchar(255)
*	CHAR:x		=> char(x)
*	XSTEXT_UNI	=> text for storing 100 characters (topic_title for example)
*	STEXT_UNI	=> text for storing 255 characters (normal input field with a max of 255 single-byte chars) - same as VCHAR_UNI
*	TEXT_UNI	=> text for storing 3000 characters (short text, descriptions, comments, etc.)
*	MTEXT_UNI	=> mediumtext (post text, large text)
*	VCHAR:x		=> varchar(x)
*	TIMESTAMP	=> int(11) UNSIGNED
*	DECIMAL		=> decimal number (5,2)
*	DECIMAL:	=> decimal number (x,2)
*	PDECIMAL	=> precision decimal number (6,3)
*	PDECIMAL:	=> precision decimal number (x,3)
*	VCHAR_UNI	=> varchar(255) BINARY
*	VCHAR_CI	=> varchar_ci for postgresql, others VCHAR
*/

function get_schema()
{
	$schema_data = array();
	
/*
CREATE TABLE phpbb_shoutbox (
	shout_id int(11) unsigned NOT NULL auto_increment,
	shout_user_id mediumint(8) NOT NULL,
	shout_time int(11) NOT NULL,
	shout_ip varchar(32) character set latin1 NOT NULL,
	shout_text text collate utf8_bin NOT NULL,
	shout_bbcode_bitfield varchar(255) character set latin1 NOT NULL,
	shout_bbcode_uid varchar(8) character set latin1 NOT NULL,
	shout_bbcode_flags int(11) unsigned NOT NULL default '7',
	PRIMARY KEY	(shout_id)
) DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
*/
	$schema_data['phpbb_shoutbox'] = array(
		'COLUMNS'   => array(
		    'shout_id'		=> array('UINT', NULL, 'auto_increment'),
		    'shout_user_id'	=> array('UINT', 0),
			'shout_time'    => array('TIMESTAMP', 0),
			'shout_ip'      => array('VCHAR:40', ''),
			'shout_text'    => array('MTEXT_UNI', ''),
			'shout_bbcode_bitfield'     => array('VCHAR:255', ''),
			'shout_bbcode_uid'          => array('VCHAR:8', ''),
			'shout_bbcode_flags'        => array('UINT:11', 7),
		),
		'PRIMARY_KEY'	=> 'shout_id',
	);
    return $schema_data;
}
?>