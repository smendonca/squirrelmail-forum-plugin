<?php
/**
 * config.php
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
 * Database configuration
 * - driver
 * - username
 * - password
 * - host
 * - name
 */
$db_configuration = array(
	'phptype' => 'mysql',
	'username' => 'root',
	'password' => 'rootmdp',
	'hostspec' => 'localhost',
	'database' => 'sqmail_forum',
);

/**
 * Administrators who can manipulate forums
 */
$administrators = array(
	'george',
	'jacques',
);

/**
 * Number of thread to show per page.
 */
$pg_thread_number = '30';
?>
