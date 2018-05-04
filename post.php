<?php
/**
 * post.php
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

// FORUM
if (file_exists(SM_PATH.'plugins/forum/config.php')) {
	include_once SM_PATH.'plugins/forum/config.php';
} else {
	displayPageHeader($color, 'none');
	plain_error_message('This plugin is not correctly configured.', $color);
	exit;
}

include_once SM_PATH.'plugins/forum/functions.php';

if (sqGetGlobalVar('QUERY_STRING', $QUERY_STRING, SQ_SERVER)) {
	$forum_id = preg_replace("[^0-9]", '', $QUERY_STRING);
}

if (!isset($forum_id) || !check_forum_id($forum_id)) {
	displayPageHeader($color, 'none');
	plain_error_message('Bad request, the link you followed is incorrect or outdated.', $color);
	exit;
}

if (sqGetGlobalVar('post', $post, SQ_POST)) {
	sqGetGlobalVar('body', $body, SQ_POST);
	sqGetGlobalVar('subject', $subject, SQ_POST);
	$subject = trim($subject);
	$body = trim($body);
	if (!empty($subject) && !empty($body) && $username) {
		insert_thread($subject, $body, $username, $forum_id);
		unset($subject, $body);
		header('location: list.php?'.$forum_id);
	}
}

displayPageHeader($color, 'none');

bindtextdomain('forum', SM_PATH.'locale');
textdomain('forum');

if (isset($post)) {
	plain_error_message(_("Please fill in the subject and the body."), $color);
	echo '<br />';
}

if (!isset($subject)) {
	$subject = '';
}

if (!isset($body)) {
	$body = '';
}

$forum = get_forum($forum_id);

echo html_tag('table',
		html_tag('tr',
			html_tag('td', '&nbsp;<a href="forum.php" style="text-decoration: none;">'._("Forum list").'</a>&nbsp;-&nbsp;<a href="list.php?'.$forum_id.'" style="text-decoration: none;">'._("Thread list").'</a>', 'left', $color[0])
		),
	'center', '', 'cellspacing="0" cellpadding="2" width="650" style="border: 1px solid '.$color[9].';"');

echo '<form action="post.php?'.$forum_id.'" method="post">'.
		html_tag('table',
			html_tag('tr',
				html_tag('th', $forum['forum_name'], 'left', $color[0])
			).
			html_tag('tr',
				html_tag('td', $forum['forum_description'], 'left', $color[0])
			).
			html_tag('tr',		
				html_tag('td', '&nbsp; &nbsp;'._("Subject").':&nbsp;<input type="text" name="subject" size="60" maxlength="255" value="'.$subject.'" />', 'left', $color[0], 'style="border-bottom: 1px solid '.$color[9].';"')
			).
			html_tag('tr',				
				html_tag('td', '<textarea name="body" cols="86" rows="13">'.$body.'</textarea>', 'center')
			).			
			html_tag('tr',
				html_tag('td', '<input type="submit" name="post" value="'._("Post new topic").'" />&nbsp;','right', '', 'style="padding: 10px 0; border-top: 1px solid '.$color[9].';"')
			),
		'center', '', 'width="650" cellpadding="5" cellspacing="0" style="border: 1px solid '.$color[9].'; margin-top: 1px;"').
	'</form>';
?>
</body></html>
