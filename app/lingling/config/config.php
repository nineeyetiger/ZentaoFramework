<?php
/**
 * The config file of ZenTaoPHP.  Don't modify this file directly, copy the item to my.php and change it.
 *
 * The author disclaims copyright to this source code.  In place of
 * a legal notice, here is a blessing:
 * 
 *  May you do good and not evil.
 *  May you find forgiveness for yourself and forgive others.
 *  May you share freely, never taking more than you give.
 */
/* Basic settings. */
$config->version     = '2.1';             // The version of zentaophp. Don't change it.
//$config->encoding    = 'UTF-8';           // The encoding of znetaopms.
$config->encoding    = 'GBK';           // The encoding of znetaopms.
$config->cookieLife  = time() + 2592000;  // The cookie life time.
$config->timezone    = 'Asia/Shanghai';   // The time zone setting, for more see http://www.php.net/manual/en/timezones.php

/* The request settings. */
$config->requestType = 'PATH_INFO';       // The request type: PATH_INFO|GET, if PATH_INFO, must use url rewrite.
$config->pathType    = 'clean';           // If the request type is PATH_INFO, the path type.
$config->requestFix  = '-';               // The divider in the url when PATH_INFO.
$config->moduleVar   = 'm';               // requestType=GET: the module var name.
$config->methodVar   = 'f';               // requestType=GET: the method var name.
$config->viewVar     = 't';               // requestType=GET: the view var name.
$config->sessionVar  = 'sid';             // requestType=GET: the session var name.

/* Views and themes. */
$config->views  = ',html,json,';          // Supported view formats.
$config->themes = 'default';              // Supported themes.

/* Supported languages. */
$config->langs['zh-cn'] = '中文简体';
$config->langs['en']    = 'English';

/* Default settings. */
$config->default->view   = 'html';        // Default view.
$config->default->lang   = 'en';          // Default language.
$config->default->theme  = 'default';     // Default theme.
$config->default->module = 'user';       // Default module.
$config->default->method = 'admin';       // Default method.

/* Database settings. */
$config->db->persistant = false;           // Pconnect or not.
$config->db->driver     = 'mysql';         // Must be MySQL. Don't support other database server yet.
$config->db->encoding   = 'UTF8';          // Encoding of database.
$config->db->strictMode = false;           // Turn off the strict mode of MySQL.
$config->db->prefix     = '';              // The prefix of the table name.

/* Slave database settings. */
$config->slaveDB->persistant = false;      
$config->slaveDB->driver     = 'mysql';    
$config->slaveDB->encoding   = 'UTF8';     
$config->slaveDB->strictMode = false;

$config->strictParams = 'true';

/* database table definition */
define('TABLE_TOURNAMENT', 				'tournament');
define('TABLE_TEAM',					'team');
define('TABLE_GAME',					'game');
define('TABLE_GAME_RING',				'game_ring');
define('TABLE_RING_TUNE',				'ring_tune');
define('TABLE_RING',					'ring');
define('TABLE_USER',					'user');
define('TABLE_CUSTOM_RING',				'custom_ring');
define('TABLE_PARAM',					'param');

define('TEAM_RING', 1);
define('ANDROID_RING', 2);
define('IOS_RING', 3);

define('TABLE_VOTE_ROUND', 'vote_round');
define('TABLE_VOTE_PERSON', 'vote_person');
define('TABLE_VOTE_VOTE', 'vote_vote');
define('TABLE_VOTE_GAME', 'vote_game');

/* Include the custom config file. */
$myConfig = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'my.php';
if(file_exists($myConfig)) include $myConfig;



