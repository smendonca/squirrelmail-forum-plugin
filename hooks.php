<?php
/**
 * hooks.php
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
 * Menuline initialization.
 * @return void
 */
function do_forum_menuline()
{
	bindtextdomain('forum', SM_PATH.'locale');
	textdomain('forum');
	
	displayInternalLink('plugins/forum/forum.php', _("Forum"), 'right');
	echo '&nbsp;&nbsp;&nbsp;';
	
	bindtextdomain('squirremail', SM_PATH.'locale');
	textdomain('squirrelmail');
}

/**
 * Custom preference page initialization.
 * @return void
 */
function do_forum_options() 
{
	global $optpage_blocks,$username;
	
	if (file_exists(SM_PATH.'plugins/forum/config.php')) {
	
		include_once SM_PATH.'plugins/forum/config.php';

		if (in_array($username, $administrators)) {
			bindtextdomain('forum', SM_PATH.'locale');
			textdomain('forum');
			
			$optpage_blocks[] = array(
				'name' => _("Discussion board"),
				'url' => SM_PATH.'plugins/forum/options.php',
				'desc' => _("A browser-based administrative interface."),
				'js' => false,
			);
			
			bindtextdomain('squirremail', SM_PATH.'locale');
			textdomain('squirrelmail');
		}
	}
}
?>
