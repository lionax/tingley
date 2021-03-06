<?php

	/**
	 * Project: Higher For Hire
	 * File: breadcrumbs.core.php
	 *
	**/

	class Content
	{
		private $table;
		
		function __construct()
		{
			$this->table = MYSQL_TABLE_PREFIX . "content";
		}
		
		function setup()
		{
			global $db;
			$db->query("CREATE TABLE IF NOT EXISTS `".$this->table."` (
				`key` TEXT NOT NULL ,
				`title` TEXT NOT NULL ,
				`text` TEXT NOT NULL ,
				`box_content` TEXT NOT NULL ,
				`version` INT NOT NULL ,
				`version_timestamp` INT NOT NULL ,
				`version_author` INT NOT NULL
				) ENGINE = MYISAM ;");
				
			$db->query("CREATE TABLE IF NOT EXISTS `".MYSQL_TABLE_PREFIX."content_permissions` (
				`key` VARCHAR( 255 ) NOT NULL ,
				`groupid` INT NOT NULL
				) ENGINE = MYISAM ;");
		}
		
		function pageExists($key)
		{
			global $db, $mod;
			$k = secureMySQL($key);
			
			if ($db->num_rows($this->table, "`key`='" . $k . "'") > 0) {
				return true;
			}
			else {
				if ($mod->isInstalled('formmaker')) {
					return $db->num_rows('formmaker', "`key`='".$k."'") > 0;
				}
				else {
					return false;
				}
			}
		}
		
		function getPage($key, $version = -1)
		{
			global $db, $rights;
			$k = secureMySQL($key);
			$v = (int)$version;
			
			if ($version == -1) {
				$page = $db->selectOneRow($this->table, "*", "`key`='" . $k . "'", "`version` DESC");
			}
			else {
				$page = $db->selectOneRow($this->table, "*", "`key`='" . $k . "' AND `version`=".$v);
			}
			
			$page['versions'] = $this->getVersions($key);
			return $page;
		}
		
		function getVersions($key) {
			global $db;
			$k = secureMySQL($key);
			$tbl_user = MYSQL_TABLE_PREFIX.'users';
			$sql = "SELECT cnt.`version`, cnt.`version_timestamp`, cnt.`version_author`, cnt.`title`, usr.`nickname` AS author
					FROM `".$this->table."` AS cnt
					LEFT JOIN `".$tbl_user."` AS usr
					ON cnt.version_author = usr.userid
					WHERE `key`='".$k."'
					ORDER BY `version_timestamp` DESC";
			return $db->queryToList($sql);
		}
		
		function getPages()
		{
			global $db;
			$pages = $db->queryToList("SELECT `key` AS `k`, `title`, (SELECT count(`key`) 
										FROM ".MYSQL_TABLE_PREFIX."content WHERE `key`=`k`) AS `version_count` 
										FROM `".MYSQL_TABLE_PREFIX."content` GROUP BY `key` ORDER BY `key`");
			
			if (count($pages) > 0)
			foreach ($pages as $i => $v)
			{
				$pages[$i]['title'] = cutString($v['title']);
				$pages[$i]['edit_url'] = makeURL('admin', array('mode' => 'content', 'action' => 'edit', 'key' => $v['k']));
				$pages[$i]['remove_url'] = makeURL('admin', array('mode' => 'content', 'action' => 'remove', 'key' => $v['k']));
				$pages[$i]['url'] = makeURL($v['k']);
			}
			
			return $pages;
		}
		
		function getPageList()
		{
			global $db;
			
			$pages = $db->queryToList("SELECT `key`, `title`, `key` AS `mod` FROM `".MYSQL_TABLE_PREFIX."content` GROUP BY `key` ORDER BY `key`");
			return $pages;
		}
		
		function createPage($title, $text, $assigned_groupid = array(0), $key = '', $box_content = '')
		{
			global $db, $login;
			
			if ($key == '')
				$k = stringToURL($title);
			else
				$k = secureMySQL($key);
				
			$ti = secureMySQL($title);
			$te = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $text) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
			$bc = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $box_content) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
			
			$sql = "INSERT INTO `".$this->table."` (`key`, `title`, `text`, `box_content`, `version`, `version_timestamp`, `version_author`) 
			VALUES ('".$k."', '".$ti."', '".$te."', '".$bc."', 0, ".time().", ".$login->currentUserId().");";
			$db->query($sql);
			
			foreach ($assigned_groupid as $group) {
				$db->insert('content_permissions', 
					array('key', 'groupid'),
					array("'".$k."'", $group)
				);
			}
			
			return $key;
		}
		
		function removePage($key)
		{
			global $db;
			$k = secureMySQL($key);
			
			$db->delete($this->table, "`key`='" . $k . "'");
		}
		
		function editPage($key, $title, $text, $assigned_groupid = array(0), $newKey = '', $box_content = '')
		{
			global $db, $login;
			$k = secureMySQL($key);
			$ti = secureMySQL($title);
			$te = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $text) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
			$nk = trim(secureMySQL($newKey));
			$bc = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $box_content) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
			
			$page = $this->getPage($key);
			
			$sql = "INSERT INTO `".$this->table."` (`key`, `title`, `text`, `box_content`, `version`, `version_timestamp`, `version_author`) 
			VALUES ('".$nk."', '".$ti."', '".$te."', '".$bc."', ".($page['version'] + 1).", ".time().", ".$login->currentUserId().");";
			$db->query($sql);
			
			$db->delete('content_permissions', "`key`='".$nk."'");
			foreach ($assigned_groupid as $group) {
				$db->insert('content_permissions', 
					array('key', 'groupid'),
					array("'".$nk."'", $group)
				);
			}
		}
	}
	
?>