<?php
/**
 * setup.php
 *
 * @copyright 2006 Jacques Mendelsohn
 * @author Jacques Mendelsohn
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @version 20060409
 * @package plugins
 * @subpackage forum
 * @category add-ons
 */

/**
 * Plugin initialization.
 * @return void
 */ 
function squirrelmail_plugin_init_forum()
{
	global $squirrelmail_plugin_hooks;

	$squirrelmail_plugin_hooks['menuline']['forum'] = 'forum_menuline';
	$squirrelmail_plugin_hooks['optpage_register_block']['forum'] = 'forum_options';
}

/**
 * Menuline function.
 */
function forum_menuline()
{
	include_once SM_PATH.'plugins/forum/hooks.php';

	do_forum_menuline();
}

/**
 * Preference page function.
 */
function forum_options() 
{	
	include_once SM_PATH.'plugins/forum/hooks.php';

	do_forum_options();
}

/**
 * Returns info about this plugin.
 * @return array
 */
function forum_info()
{
	return array(
		'english_name' => 'Forum',
		'version' => '1.1',
		'required_sm_vesion' => '1.2.7',
		'requires_configuration' => 1,
		'requires_source_patch' => 0,
		'required_php_version' => '4.3.2',
		'required_pear_packages' => array('MDB2'),
		'summary' => 'Integrates forum functionality into SquirrelMail.',
		'details' => 'This plugin is a light discussion board that integrates seamlessly into SquirrelMail. All forum data is stored in a database backend of your choice.',
	);
}

/**
 * Returns version info about this plugin.
 * @return string
 */
function forum_version()
{
	$info = forum_info();
	
	return $info['version'];
}
?>
