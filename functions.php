<?php
/**
 * functions.php
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
 * Make a database connection.
 * Exit if Pear MDB2 package cannot be included.
 * @return object
 * @access private
 */
function database_connection()
{
	global $db_configuration;
	
	if (!(@include_once 'MDB2.php')) {
		exit('Unable to find Pear MDB2 package.');	
	}
	
	$MDB2 =& MDB2::connect($db_configuration, array('debug' => 2, 'portability' => MDB2_PORTABILITY_ALL));
	
	if (MDB2::isError($MDB2)) {       
		exit('Could not make database connection.');
	}
	
	$MDB2->setFetchMode(MDB2_FETCHMODE_ASSOC);
	
	return $MDB2;
}

/** 
 * Show all forums.
 * @param boolean
 * @return array
 * @access private
 */
function get_forums($visible = true)
{
	$MDB2 = database_connection();
	$forums = $MDB2->queryAll('SELECT forum_id,
									forum_name,
									forum_description,
									display_order
								FROM sqmf_forum '.
								(($visible) ? 'WHERE forum_visible=1' : '')
								.' ORDER BY display_order,forum_id ASC;');
	for ($i=0; $i<count($forums); $i++) {
		$forums[$i]['thread_count'] = $MDB2->queryOne("SELECT COUNT(thread_id) 
														FROM sqmf_thread 
														WHERE forum_id='".(int)$forums[$i]['forum_id']."';");
		$forums[$i]['last_thread'] = $MDB2->queryOne("SELECT MAX(thread_date)
														FROM sqmf_thread
														WHERE forum_id='".(int)$forums[$i]['forum_id']."';");
	}
	$MDB2->disconnect();

	return $forums;
}

/**
 * Show one forum data.
 * @param string
 * @return array
 * @access private 
 */
function get_forum($forum_id)
{
	$MDB2 = database_connection();
	$forum = $MDB2->queryRow("SELECT forum_id,
									forum_name,
									forum_description,
									forum_visible
								FROM sqmf_forum
								WHERE forum_id='".(int)$forum_id."';");
	$MDB2->disconnect();
	
	return $forum;
}

/**
 * Insert a new forum in database.
 * @param string
 * @param string
 * @param string 
 * @access private
 */
function add_forum($name, $description, $visible)
{
	$MDB2 = database_connection();
	$MDB2->query("INSERT INTO sqmf_forum
					SET forum_name='".addslashes($name)."', 
						forum_description='".addslashes($description)."',
						forum_visible='".((empty($visible)) ? 0 : 1)."';");
	$MDB2->disconnect();
}

/**
 * Delete forum with all its threads and posts.
 * @param string
 * @access private 
 */
function del_forum($forum_id)
{
	$MDB2 = database_connection();
	$threads = $MDB2->queryAll("SELECT thread_id
								FROM sqmf_thread 
								WHERE forum_id='".(int)$forum_id."';");	
	for ($i=0; $i<count($threads); $i++) {
		$MDB2->query("DELETE FROM sqmf_post WHERE thread_id='".(int)$threads[$i]['thread_id']."';");
	}
	$MDB2->query("DELETE FROM sqmf_thread WHERE forum_id='".(int)$forum_id."';");
	$MDB2->query("DELETE FROM sqmf_forum WHERE forum_id='".(int)$forum_id."';");
	$MDB2->disconnect();
}

/**
 * Update forum name and description.
 * @param string
 * @param string
 * @param string 
 * @param string
 * @access private 
 */
function update_forum($forum_id, $name, $description, $visible)
{
	$MDB2 = database_connection();
	$MDB2->query("UPDATE sqmf_forum
					SET forum_name='".addslashes($name)."', 
						forum_description='".addslashes($description)."',
						forum_visible='".((empty($visible)) ? 0 : 1)."'
					WHERE forum_id='".(int)$forum_id."';");
	$MDB2->disconnect();
}

/** 
 * Check if forum ID exists.
 * @param string
 * @return boolean
 * @access private
 */
function check_forum_id($forum_id)
{
	$MDB2 = database_connection();
	if ($MDB2->queryOne("SELECT COUNT(forum_id) 
							FROM sqmf_forum 
							WHERE forum_id='".(int)$forum_id."';")) {
		$MDB2->disconnect();
		return true;		
	} else {
		$MDB2->disconnect();
		return false;
	}
}

/**
 * Get all threads of a page of a forum.
 * @param string
 * @param string
 * @return array
 * @access private 
 */
function get_forum_threads($forum_id, $current = 1)
{
	global $pg_thread_number;
	
	if (empty($current)) {
		$current = 1;
	}
	
	$MDB2 = database_connection();
	// recherche le nombre total de thread
	$thread_count = $MDB2->queryOne("SELECT COUNT(thread_id) 
										FROM sqmf_thread
										WHERE forum_id='".(int)$forum_id."';");
	// calcul du nombre total de page
	if ($thread_count > 0) {
		$total = ceil($thread_count / $pg_thread_number);
	} else {
		$total = 1;
	}
	// calcul de la limite en fonction de la page demandée
	if ($thread_count > $pg_thread_number && $current > 0 && $current <= $total) {
		$line = ($current - 1) * $pg_thread_number;		
		$previous = 1;
		$next = 1;
		if ($current - 1 > 0) {
			$previous = $current - 1;
		}
		if ($current + 1 <= $total) {
			$next = $current + 1;
		} elseif ($current == $total) {
			$next = $current;
		}
	} else {
		$current = 1;
		$line = 0;
		$previous = 1;
		$next = 1;
	}
	// requête	
	$MDB2->setLimit($pg_thread_number, $line);
	$threads = $MDB2->queryAll("SELECT thread_id,
									thread_login,
									thread_date,
									thread_title,
									nb_post,
									nb_view,
									last_post_date,
									last_post_login
								FROM sqmf_thread 
								WHERE forum_id='".addslashes($forum_id)."' 
								ORDER BY last_post_date DESC;");	
	$MDB2->disconnect();
	
	return array($threads,
				array(
					'total' => $total,
					'previous' => $previous,
					'next' => $next,
					'current' => $current
					)
			);
}

/**
 * Check if threads ID exists.
 * @param string
 * @return boolean
 * @access private 
 */
function check_thread_id($thread_id)
{
	$MDB2 = database_connection();
	if ($MDB2->queryOne("SELECT COUNT(thread_id) 
							FROM sqmf_thread 
							WHERE thread_id='".(int)$thread_id."';") == 1) {
		$MDB2->disconnect();
		return true;
	} else {
		$MDB2->disconnect();
		return false;
	} 
}

/**
 * Increment view counter.
 * @param string
 * @access private 
 */
function increment_view_counter($thread_id)
{
	$MDB2 = database_connection();	
	$MDB2->query("UPDATE sqmf_thread 
					SET nb_view=nb_view + 1 
					WHERE thread_id='".(int)$thread_id."';");
	$MDB2->disconnect();
}

/**
 * Show one thread.
 * @param string
 * @return array
 * @access private 
 */
function get_thread($thread_id)
{
	$MDB2 = database_connection();
	$thread = $MDB2->queryRow("SELECT thread_id,
									thread_login,
									thread_date,
									thread_title,
									nb_post,
									nb_view,
									last_post_date,
									last_post_login,
									thread_content,
									forum_id 
								FROM sqmf_thread 
								WHERE thread_id='".(int)$thread_id."';");
	$MDB2->disconnect();

	return $thread;
}

/**
 * Show all posts of a thread.
 * @param string
 * @return array
 * @access private 
 */
function get_thread_posts($thread_id)
{
	$MDB2 = database_connection();
	$posts = $MDB2->queryAll("SELECT sqmf_post.post_id,
									sqmf_post.post_login,
									sqmf_post.post_date,
									sqmf_post.post_content,
									(sqmf_stat.stat_post + sqmf_stat.stat_thread) AS nb_message 
								FROM sqmf_post,sqmf_stat 
								WHERE sqmf_post.thread_id='".(int)$thread_id."' 
								AND sqmf_stat.stat_login=sqmf_post.post_login 
								ORDER BY sqmf_post.post_id ASC;");
	$MDB2->disconnect();

	return $posts;
}

/**
 * Insert a new post in database.
 * @param string
 * @param string
 * @param string
 * @access private 
 */
function insert_post($body, $login, $thread_id) 
{
	$MDB2 = database_connection();
	if ($MDB2->queryOne("SELECT COUNT(stat_login) 
							FROM sqmf_stat 
							WHERE stat_login='".addslashes($login)."';") == 1) {
		// mise à jour des données concernant l'utilisateur dans la table des statistiques
		$MDB2->query("UPDATE sqmf_stat 
						SET stat_post=stat_post + 1 
						WHERE stat_login='".addslashes($login)."';");
	} else {
		// insertion d'une ligne concernant l'utilisateur dans la table des statistiques
		$MDB2->query("INSERT INTO sqmf_stat 
						SET stat_post=1,
							stat_thread=0,
							stat_login='".addslashes($login)."';");
	}
	// ajoute le post
	$MDB2->query("INSERT INTO sqmf_post 
					SET post_login='".addslashes($login)."',
						post_date='".gmdate('Y/m/d H:i:s')."',
						post_content='".addslashes($body)."',
						thread_id='".(int)$thread_id."';");
	// incrémente le compteur du nombre de post du thread correspondant
	$MDB2->query("UPDATE sqmf_thread 
					SET last_post_date='".gmdate('Y/m/d H:i:s')."',
						last_post_login='".addslashes($login)."',
						nb_post=nb_post + 1 
					WHERE thread_id='".(int)$thread_id."';");
	$MDB2->disconnect();
}

/**
 * Insert a new thread in database.
 * @param string
 * @param string
 * @param string
 * @param string
 * @access private
 */
function insert_thread($subject, $body, $login, $forum_id) 
{
	$MDB2 = database_connection();
	if ($MDB2->queryOne("SELECT COUNT(stat_login) 
							FROM sqmf_stat 
							WHERE stat_login='".addslashes($login)."';") == 1) {
		// mise à jour des données concernant l'utilisateur dans la table des statistiques
		$MDB2->query("UPDATE sqmf_stat 
						SET stat_thread=stat_thread + 1 
						WHERE stat_login='".addslashes($login)."';");
	} else {
		// insertion d'une ligne concernant l'utilisateur dans la table des statistiques
		$MDB2->query("INSERT INTO sqmf_stat 
						SET stat_post=0,
							stat_thread=1,
							stat_login='".addslashes($login)."';");
	}
	// ajoute le thread
	$MDB2->query("INSERT INTO sqmf_thread 
					SET thread_login='".addslashes($login)."',
						thread_date='".gmdate('Y/m/d H:i:s')."',
						thread_title='".addslashes($subject)."',
						thread_content='".addslashes($body)."',
						forum_id='".addslashes($forum_id)."',
						last_post_date='".gmdate('Y/m/d H:i:s')."',
						last_post_login='".addslashes($login)."';");
	$MDB2->disconnect();
}

/**
 * Show date with SquirrelMail date format function.
 * @param date
 * @return date
 * @access private 
 */
function print_date($date)
{
	bindtextdomain('squirrelmail', SM_PATH.'locale');
	textdomain('squirrelmail');
	
	$year = substr($date, 0, 4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);
	$hour = substr($date, 11, 2);
	$minute = substr($date, 14, 2);		
	
	$tzc = date('O');

	$iTzc = (substr($tzc, 1, 2) * 60 + substr($tzc, 3, 2)) * 60;
	
    if (substr($tzc, 0, 1) == '-') {
		$timestamp = mktime($hour, $minute, 0, $month, $day, $year) - $iTzc;
    } else {
		$timestamp = mktime($hour, $minute, 0, $month, $day, $year) + $iTzc;
    }
	
	$date = getDateString($timestamp);
	
	bindtextdomain('forum', SM_PATH.'locale');
	textdomain('forum');
	
	return $date;
}
?>
