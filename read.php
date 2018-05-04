<?php
/**
 * read.php
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

if (sqGetGlobalVar('QUERY_STRING', $QUERY_STRING, SQ_SERVER)) {
	$thread_id = ereg_replace("[^0-9]", '', $QUERY_STRING);
}

if (!is_numeric($thread_id) || !check_thread_id($thread_id)) {
	exit('Bad request, the link you followed is incorrect or outdated.');
}

if (sqGetGlobalVar('reply', $reply, SQ_POST)) {
	sqGetGlobalVar('body', $body, SQ_POST);		
	$body = trim($body);
	if (!empty($body) && $username) {	
		insert_post($body, $username, $thread_id);
		unset($body);
	} else {
		plain_error_message(_("Please fill in the body of your reply."), $color);
		echo '<br />';
	}
} else {
	increment_view_counter($thread_id);
}

$thread = get_thread($thread_id);
$posts = get_thread_posts($thread_id);

echo html_tag('table',
		html_tag('tr',
			html_tag('td', '&nbsp;<a href="forum.php" style="text-decoration: none;">'._("Forum list").'&nbsp;-&nbsp;<a href="list.php?'.$thread['forum_id'].'" style="text-decoration: none;">'._("Thread list").'</a>', 'left', $color[0])
		),
	'center', '', 'cellspacing="0" cellpadding="2" width="80%" style="border: 1px solid '.$color[9].';"');

echo html_tag('table',
		html_tag('tr',
			html_tag('td', print_date($thread['thread_date']), 'left', $color[0], 'width="18%" style="padding-left: 5px;"').
			html_tag('td', '<b>'.$thread['thread_title'].'</b>&nbsp;&nbsp;', 'right', $color[0])
		).
		html_tag('tr',
			html_tag('td', $thread['thread_login'], 'left', $color[12], 'style="padding: 5px;"').
			html_tag('td', nl2br(stripslashes($thread['thread_content'])), 'left', '', 'style="padding: 5px;"')
		),
	'center', '', 'width="80%" cellspacing="0" cellpadding="2" style="border: 1px solid '.$color[9].'; margin-top: 1px;"');

if (!empty($posts[0]['post_id'])) {
	for ($i=0; $i<count($posts); $i++) {
		echo html_tag('table',
				html_tag('tr',
					html_tag('td', print_date($posts[$i]['post_date']), 'left', $color[0], 'width="18%" style="padding-left: 5px;"').
					html_tag('td', '#'.$posts[$i]['post_id'].'&nbsp;&nbsp;', 'right', $color[0], 'style="color: '.$color[9].';"')
				).
				html_tag('tr',
					html_tag('td', $posts[$i]['post_login'], 'left', $color[12], 'style="padding: 5px;"').
					html_tag('td', nl2br(stripslashes($posts[$i]['post_content'])), '', '', 'style="padding: 5px;"'),
				'', '', 'valign="top"'),
			'center', '', 'width="80%" cellspacing="0" cellpadding="2" style="border: 1px solid '.$color[9].'; margin-top: 3px;"');
	}
}

echo '<form action="read.php?'.$thread_id.'" method="post">'.
		html_tag('table',
			html_tag('tr',
				html_tag('th', _("Reply"), 'left', $color[0], 'style="padding: 5px; border-bottom: 1px solid '.$color[9].';"')
			).
			html_tag('tr',
				html_tag('td', '<textarea name="body" cols="86" rows="7"></textarea>', 'center', '', 'style="padding: 5px 4px;"')
			).
			html_tag('tr',
				html_tag('td', '<input type="submit" name="reply" value="'._("Post message").'" />&nbsp;','right', '', 'style="padding: 5px 0; border-top: 1px solid '.$color[9].';"')
			),
		'center', '', 'width="600" cellpadding="3" cellspacing="0" style="margin-top: 20px; border: 1px solid '.$color[9].';"').
	'</form>';
?>					
</body></html>	
