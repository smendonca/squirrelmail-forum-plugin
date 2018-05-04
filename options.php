<?php
/**
 * options.php
 *
 * @copyright 2006 Jacques Mendelsohn
 * @author Jacques Mendelsohn
 * @license http://www.gnu.org/licenses/gpl.txt GNU Public License
 * @version 20060409
 * @package plugins
 * @subpackage forum
 * @category add-ons
 */

// SQUIRRELMAIL
if (file_exists('../../include/init.php')) {
	include_once '../../include/init.php';
} elseif (file_exists('../../include/validate.php')) {
	define('SM_PATH', '../../');
	include_once SM_PATH.'include/validate.php';
} else {
	chdir('..');
	define('SM_PATH', '../');
	include_once SM_PATH.'src/validate.php';
}

if (!in_array('forum', $plugins)) {
	plain_error_message('This plugin is not enabled in the SquirrelMail configuration.', $color);
	exit;
}

include_once SM_PATH.'functions/date.php';

displayPageHeader($color, 'none');

// FORUM
if (file_exists(SM_PATH.'plugins/forum/config.php')) {
	include_once SM_PATH.'plugins/forum/config.php';
} else {
	plain_error_message('This plugin is not correctly configured.', $color);
	exit;
}

include_once SM_PATH.'plugins/forum/functions.php';

bindtextdomain('forum', SM_PATH.'locale');
textdomain('forum');

if (!in_array($username, $administrators)) {
	plain_error_message('You are not an administrator.', $color);
	exit;
}

// add
if (sqGetGlobalVar('action', $action, SQ_POST)) {
	switch ($action) {
		case 'add':
			sqGetGlobalVar('add_name', $add_name, SQ_POST);
			sqGetGlobalVar('add_description', $add_description, SQ_POST);
			sqGetGlobalVar('add_visible', $add_visible, SQ_POST);
			$add_name = trim($add_name);
			$add_description = trim($add_description);
			if (!empty($add_name) && !empty($add_description)) {						
				add_forum($add_name, $add_description, $add_visible);
				unset($add_name, $add_description, $add_visible);			
			} else {
				$error = _("Please fill in the name and the description.");
			}
		break;
		case 'modify':
			sqGetGlobalVar('mod_name', $mod_name, SQ_POST);
			sqGetGlobalVar('mod_description', $mod_description, SQ_POST);
			sqGetGlobalVar('mod_visible', $mod_visible, SQ_POST);
			sqGetGlobalVar('forum_id', $forum_id, SQ_POST);
			$mod_name = trim($mod_name);
			$mod_description = trim($mod_description);
			if (!empty($mod_name) && !empty($mod_description)) {			
				update_forum($forum_id, $mod_name, $mod_description, $mod_visible);
			} else {
				$action = 'action_mod';
				$error = _("Please fill in the name and the description.");
			}
		break;
		case 'delete':
			sqGetGlobalVar('forum_id', $forum_id, SQ_POST);
			del_forum($forum_id);
		break;
	}
}

if (sqGetGlobalVar('action_mod', $action_mod, SQ_POST)) {
	sqGetGlobalVar('forum_id', $forum_id, SQ_POST);
	$forum = get_forum($forum_id);
	$mod_name = $forum['forum_name'];
	$mod_description = $forum['forum_description'];
	$mod_visible = $forum['forum_visible'];
	$action = 'action_mod';
}

if (sqGetGlobalVar('action_del', $action_mod, SQ_POST)) {
	sqGetGlobalVar('forum_id', $forum_id, SQ_POST);
	$forum = get_forum($forum_id);
	$del_name = $forum['forum_name'];
	$action = 'action_del';
}

$forums = get_forums(false);

echo html_tag('table',
		html_tag('tr',
			html_tag('th', _("Options - Discussion board"), 'center', $color[0])
		),
	'center', '', 'width="100%" cellpadding="2" cellspacing="1"').
	'<br /><br />';

if (isset($error)) {	
	plain_error_message($error, $color);
	echo '<br />';
}		

if (!isset($add_name)) {
	$add_name = '';
}

if (!isset($add_description)) {
	$add_description = '';
}

if (!isset($add_visible)) {
	$add_visible = '';
}

if ($action == 'action_mod') { 
	echo '<form method="post" action="'.$PHP_SELF.'">'.
			html_tag('table', '', 'center', '', 'width="60%" cellpadding="4" cellspacing="1"').
				html_tag('tr',
					html_tag('th', _("Modify forum"), 'center', $color[9])
				).
				html_tag('tr').
					html_tag('td', '', 'center', $color[0]).
						html_tag('table').			
							html_tag('tr',
								html_tag('td', _("Name:"), 'right', '', 'width="30%" style="padding: 10px 0;"').
								html_tag('td', '<input type="text" name="mod_name" value="'.$mod_name.'" size="50" />', 'left')
							).
							html_tag('tr',
								html_tag('td', _("Description:"), 'right', '', 'style="padding-bottom: 5px;"').
								html_tag('td', '<textarea cols="50" rows="5" name="mod_description">'.$mod_description.'</textarea>', 'left')
							).
							html_tag('tr',				
								html_tag('td', '&nbsp;', 'right').
								html_tag('td', '<input type="checkbox" name="mod_visible" '.(($mod_visible) ? 'checked="checked"' : '') .' id="visibleclic" />&nbsp;<label for="visibleclic">'._("Forum will be visible").'</label>', 'left')
							).							
							html_tag('tr',
								html_tag('td', '<input type="submit" value="'._("Modify forum").'" />','center', '', 'style="padding-bottom: 5px;" colspan="2"')
					).
			'</table></td></tr></table><input type="hidden" name="action" value="modify" /><input type="hidden" name="forum_id" value="'.$forum_id.'" /></form><br /><br />';
} elseif ($action == 'action_del') {
	echo '<form method="post" action="'.$PHP_SELF.'">'.
			html_tag('table', '', 'center', '', 'width="60%" cellpadding="4" cellspacing="1"').
				html_tag('tr',
					html_tag('th', _("Modify forum"), 'center', $color[9])
				).
				html_tag('tr',
					html_tag('td', '<br />'._("Selected forum:").'&nbsp;<b>'.$del_name.'</b><br />'._("Confirm delete of selected forum?").'<br /><br /><input type="submit" value="'._("Confirm delete").'" /><br /><br />', 'center', $color[0])
				).
		'<input type="hidden" name="action" value="delete" /><input type="hidden" name="forum_id" value="'.$forum_id.'" /></table></form><br />';
				
			
} else {
	echo '<form method="post" action="'.$PHP_SELF.'">'.
			html_tag('table', '', 'center', '', 'width="60%" cellpadding="4" cellspacing="1"').
				html_tag('tr',
					html_tag('th', _("Create forum"), 'center', $color[9])
				).
				html_tag('tr').
					html_tag('td', '', 'center', $color[0]).
						html_tag('table').			
							html_tag('tr',
								html_tag('td', _("Name:"), 'right', '', 'width="30%" style="padding: 10px 0;"').
								html_tag('td', '<input type="text" name="add_name" value="'.$add_name.'" size="50" />', 'left')
							).
							html_tag('tr',
								html_tag('td', _("Description:"), 'right', '', 'style="padding-bottom: 5px;"').
								html_tag('td', '<textarea cols="50" rows="5" name="add_description">'.$add_description.'</textarea>', 'left')
							).
							html_tag('tr',				
								html_tag('td', '&nbsp;', 'right').
								html_tag('td', '<input type="checkbox" name="add_visible" '.(($add_visible) ? 'checked="checked"' : '').' id="visibleclic" />&nbsp;<label for="visibleclic">'._("Forum will be visible").'</label>', 'left')
							).			
							html_tag('tr',
								html_tag('td', '<input type="submit" value="'._("Add this forum").'" />','center', '', 'style="padding-bottom: 5px;" colspan="2"')
					).
			'</table></td></tr></table><input type="hidden" name="action" value="add" /></form><br /><br />';
						
	echo '<form method="post" action="'.$PHP_SELF.'">'.
			html_tag('table', '', 'center', '', 'width="60%" cellpadding="4" cellspacing="1"').
				html_tag('tr',
					html_tag('th', _("Modify forum"), 'center', $color[9])
				).
				html_tag('tr').
					html_tag('td', '', 'center', $color[0]);				
	if (isset($forums[0]['forum_name'])) {
		echo '<b>'._("Forum name").'</b>:&nbsp;<select name="forum_id">';
		for ($i=0; $i<count($forums); $i++) {
			echo '<option value="'.$forums[$i]['forum_id'].'">'.stripslashes($forums[$i]['forum_name']).'</option>';
		}
		echo '</select>&nbsp;&nbsp;<input type="submit" name="action_mod" value="'._("Modify").'" />&nbsp;&nbsp;<input type="submit" name="action_del" value="'._("Delete").'" />';
	} else {
		echo _("No forum found.");
	}
	echo '</td></tr></table></form>';
}
?>
</body></html>
