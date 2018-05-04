<?php
/**
 * forum.php
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

$forums = get_forums();

echo html_tag('table', '', 'center', '', 'cellspacing="0" cellpadding="2" width="80%" style="border: 1px solid '.$color[9].';"').
		html_tag('tr',
			html_tag('th', _("&nbsp;Forums"), 'left', $color[0]).
			html_tag('th', _("Threads"), 'center', $color[0]).
			html_tag('th', _("Last thread"), 'left', $color[0])
		);

for ($i=0; $i<count($forums); $i++) {
	echo html_tag('tr',
			html_tag('td', '<a href="list.php?'.$forums[$i]['forum_id'].'" style="text-decoration: none;">'.stripslashes($forums[$i]['forum_name']).'</a><br />'.stripslashes($forums[$i]['forum_description']), 'left', '', 'style="padding: 5px 10px; border-top: 1px solid '.$color[9].'; text-align: justify;" width="60%"').
			html_tag('td', $forums[$i]['thread_count'], 'center', '', 'style="border-top: 1px solid '.$color[9].';"').
			html_tag('td', print_date($forums[$i]['last_thread']), 'left', '', 'style="padding-left: 5px; border-top: 1px solid '.$color[9].';"')
		);
}
?>
	</table>
</body></html>
