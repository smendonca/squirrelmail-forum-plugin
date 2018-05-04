<?php
/**
 * list.php
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
	$QUERY_STRING = ereg_replace("[^A-Za-z0-9,]", '', $QUERY_STRING);
	if (strpos($QUERY_STRING, ',')) {
		list($forum_id, $page['current']) = explode(',', $QUERY_STRING);
	} else {
		$forum_id = $QUERY_STRING;
		$page['current'] = 1;
	}
}

if (!isset($forum_id) || !check_forum_id($forum_id)) {
	exit('Bad request, the link you followed is incorrect or outdated.');
}

list($threads, $page) = get_forum_threads($forum_id, (int)$page['current']);

echo html_tag('table',
		html_tag('tr',
			html_tag('td', '&nbsp;<a href="forum.php" style="text-decoration: none;">'._('Forum list').'</a> - <a href="post.php?'.$forum_id.'" style="text-decoration: none;">'._('New thread').'</a>', 'left', $color[0])
		),
	'center', '', 'cellspacing="0" cellpadding="2" width="90%" style="border: 1px solid '.$color[9].';"');

echo html_tag('table', '', 'center', '', 'cellspacing="0" cellpadding="2" width="90%" style="border: 1px solid '.$color[9].'; margin-top: 1px;"').
		html_tag('tr').
			html_tag('td', '&nbsp;<b>'._("Current Page:").'</b> '.$page['current'].' '._("of").' '.$page['total'], 'left', $color[0]).
			html_tag('td', '', 'right', $color[0]);
if ($page['current'] > '1') {
	echo '<a href="list.php?'.$forum_id.','.$page['previous'].'" style="text-decoration: none;">'._("Previous").'</a> &nbsp;';
} else {
	echo _("Previous").' &nbsp;';
}
if ($page['current'] < $page['total']) {
	echo '<a href="list.php?'.$forum_id.','.$page['next'].'" style="text-decoration: none;">'._("Next").'</a>';
} else {
	echo _("Next");
}
echo '&nbsp;&nbsp;</td></tr></table>';

echo html_tag('table', '', 'center', '', 'cellspacing="0" cellpadding="2" width="90%" style="border: 1px solid '.$color[9].'; margin-top: 1px;"').
		html_tag('tr',
			html_tag('th', _("&nbsp;Subject"), 'left', $color[0], 'style="border-bottom: 1px solid '.$color[9].';"').
			html_tag('th', _("Views"), 'center', $color[0], 'style="border-bottom: 1px solid '.$color[9].';"').
			html_tag('th', _("Posts"), 'center', $color[0], 'style="border-bottom: 1px solid '.$color[9].';"').
			html_tag('th', _("Last post"), 'left', $color[0], 'style="border-bottom: 1px solid '.$color[9].';"')
		);
	
if (!empty($threads[0]['thread_id'])) {		
	for ($i=0; $i<count($threads); $i++) {
		echo html_tag('tr',
				html_tag('td', '<a href="read.php?'.$threads[$i]['thread_id'].'" style="text-decoration: none;">'.$threads[$i]['thread_title'].'</a>&nbsp;'._("by").'&nbsp;'.$threads[$i]['thread_login'], 'left', '', 'style="padding: 2px 5px; text-align: justify;" width="50%"').
				html_tag('td', $threads[$i]['nb_view'], 'center', '', 'style="padding: 2px;"').
				html_tag('td', $threads[$i]['nb_post'], 'center', '', 'style="padding: 2px;"').
				html_tag('td', print_date($threads[$i]['last_post_date']).'<br /><small>'._("by").'&nbsp;'.$threads[$i]['last_post_login'].'</small>', 'left', '','style="padding: 2px;"'),
			'', (($i%2 == 0) ? '' : $color[12])
		);
	}
}
?>				
	</table>
</body></html>
