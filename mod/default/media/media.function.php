<?php
	
	/* Creates a new category and returns the id of the new category */
	function createCategory($name, $parentid = 0, $assigned_groupid = array(0), $language = '')
	{
		global $db;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$n = secureMySQL($name);
		$p = (int)$parentid;
		$g = secureMySQL(implode(';', $assigned_groupid));
		$uniqid = md5(uniqid(mt_rand(), true));
		$lang = secureMySQL($language);
		
		$sql = "INSERT INTO `" . $tbl_cat . "`
					(`categoryid`, `parentid`, `name`, `uniqid`, `language`)
					VALUES
					(NULL, " . $p . ", '" . $n . "', '" . $uniqid . "', '" . $lang . "');";
		$db->query($sql);
		
		$catid = mysql_insert_id();
		foreach ($assigned_groupid as $group) {
			$db->insert('media_categories_permissions', 
				array('categoryid', 'groupid'),
				array($catid, $group)
			);
		}
		return $catid;
	}
	
	/* Removes a category and all child categories */
	function removeCategory($categoryid)
	{
		global $db;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$cid = (int)$categoryid;
		
		$sql = "SELECT * FROM `" . $tbl_cat . "` WHERE `parentid`=" . $cid . ";";
		$result = $db->query($sql);
		while ($row = mysql_fetch_assoc($result))
			removeCategory($row['categoryid']);
		
		$sql = "DELETE FROM `" . $tbl_cat . "` WHERE `categoryid`=" . $cid . " LIMIT 1;";
		$db->query($sql);
		$db->delete('media_categories_permissions', '`categoryid`='.$cid);
	}
	
	/* Updates a category */
	function editCategory($categoryid, $name, $assigned_groupid = array(0), $language = '')
	{
		global $db;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$n = secureMySQL($name);
		$cid = (int)$categoryid;
		$lang = secureMySQL($language);
		
		if (trim($n) != "")
		{
			$sql  = "UPDATE `" . $tbl_cat . "` SET
						`name`='" . $n . "', `language`='".$lang."' 
						WHERE `categoryid`=" . $cid . " LIMIT 1;";
			$db->query($sql);
			$db->delete('media_categories_permissions', '`categoryid`='.$cid);
			foreach ($assigned_groupid as $group) {
				$db->insert('media_categories_permissions', 
					array('categoryid', 'groupid'),
					array($cid, $group)
				);
			}
		}
	}
	
	function getCategory($categoryid)
	{
		global $db;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$c = (int)$categoryid;
		
		$cat = $db->selectOneRow($tbl_cat, "*", "`categoryid`=" . $c);
		
		return $cat;
	}
	
	/* Creates a list of all categories with the passed parentid */
	function listCategories($parentid)
	{
		global $db, $config, $current_language;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$p = (int)$parentid;
		
		$list = $db->selectList($tbl_cat, "*", "`parentid`=" . $p . " AND (`language` = '' OR `language` = '".secureMySQL($current_language)."')", "`name` ASC");
		
		if (count($list) > 0)
			foreach ($list as $i => $l)
			{
				if (isVisible($l['categoryid']))
				{
					$list[$i]['url'] = makeURL('media', array('categoryid' => $l['categoryid']));
					
					if ($config->get('media', 'hide-submedia') != '1') {
    					$childCategories = getSubCategories($l['categoryid']);
    					$list[$i]['subcategoriescount'] = count($childCategories);
					
    					$categories = $childCategories;
    					$categories[] = $l['categoryid'];
    					$catMerge = implode($categories, ",");
    					
    					$list[$i]['mediacount'] = (int)countDownloads($catMerge) + (int)countPictures($catMerge) + (int)countMovies($catMerge);
					}
					
					$list2[] = $list[$i];
				}
			}
		
		return @$list2;
	}
	
	function categoryTree()
	{
		global $db;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$result = $db->selectList($tbl_cat);
		return $result;
	}
	
	function getParent($categoryid)
	{
		$cat = getCategory($categoryid);
		if ($cat['parentid'] > 0)
			return getCategory($cat['parentid']);
		else
			return false;
	}
	
	function getParentList($categoryid)
	{
		$cat = true;
		$c = (int)$categoryid;
		$firstc = $c;
		$list = array();
		
		while (true)
		{
			$cat = getParent($c);
			if ($cat === false)
				break;
				
			$list[] = $cat;
			$c = $cat['categoryid'];
		}
		
		$list = array_reverse($list);
		$list[] = getCategory($firstc);
		return $list;
	}
	
	function getSubCategories($parentid)
	{
		global $db, $current_language;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$p = (int)$parentid;
		
		$childIds=array();
		
		$childs = $db->SelectList($tbl_cat, "*", "`parentid`=" . $p . " AND (`language` = '' OR `language` = '".$current_language."')");
		
		foreach($childs as $child) {
			$childIdsDummy = getSubCategories($child['categoryid']);
			foreach($childIdsDummy as $childDummy) {
				$childIds[] = $childDummy['categoryid'];
			}
			$childIds[] = $child['categoryid'];
		}

		return $childIds;
	}
	
	function countPictures($categoryids) {
		global $db;
		$count=0;
		$categoryids = explode(",", $categoryids);
		if(count($categoryids) > 0) {
			foreach($categoryids as $categoryid) {
				$cat = getCategory((int)$categoryid);
				$folder = $cat['uniqid'];
				@$img = scandir('media/images/'.$folder.'/');
				$count += ($img)?count($img) - 2:0;
			}
		}
		return $count;
	}

	function countSubCategories($parentid)
	{
		global $db, $current_language;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		
		$count = $db->num_rows($tbl_cat, "`parentid`=" . $cat['categoryid'] . " AND (`language`='".$current_language."' OR `language`='')");
		return $count;
	}
	
	function addDownload($categoryid, $name, $description, $file, $version ="", $release_notes = "", $thumbnail = "", $disabled = 0)
	{
		global $db, $login;
		$tbl_dl = MYSQL_TABLE_PREFIX . 'media_downloads';
		$c = (int)$categoryid;
		$n = secureMySQL($name);
		$d = secureMySQL($description);
		$f = secureMySQL($file);
		$v = secureMySQL($version);
		$rn = secureMySQL($release_notes);
		$tn = secureMySQL($thumbnail);
		$dis = (int)$disabled;
		
		if ($c > 0 && $n != "" && $f != "")
		{
			$sql = "INSERT INTO `" . $tbl_dl . "`
						(`downloadid`, `name`, `description`, `file`, `version`, `userid`, `timestamp`, `categoryid`, `release_notes`, `thumbnail`, `disabled`)
						VALUES
						(NULL, '" . $n . "', '" . $d . "', '" . $f . "', '" . $v . "', " . $login->currentUserID() . ", " . time() . ", " . $c . ", '".$rn."', '".$tn."', ".$dis.");";
			$db->query($sql);
			
			return mysql_insert_id();
		}
	}
	
	function editDownload($downloadid, $categoryid, $name, $description, $version = "", $file = "", $release_notes = "", $thumbnail = "", $disabled = 0)
	{
		global $db;
		$tbl_dl = MYSQL_TABLE_PREFIX . 'media_downloads';
		$did = (int)$downloadid;
		$c = (int)$categoryid;
		$n = secureMySQL($name);
		$d = secureMySQL($description);
		$v = secureMySQL($version);
		$f = trim(secureMySQL($file));
		$rn = secureMySQL($release_notes);
		$tn = secureMySQL($thumbnail);
		$dis = (int)$disabled;
		
		if ($did > 0 && $n != "")
		{
			if ($file == "") {
				$sql = "UPDATE `" . $tbl_dl . "`
						SET `categoryid`=" . $c . ", `name`='" . $n . "', `description`='" . $d . "', `version`='" . $v . "', `release_notes`='".$rn."', `thumbnail`='".$tn."', `disabled`=".$dis."
						WHERE `downloadid`=" . $did . ";";
			}
			else {
				$sql = "UPDATE `" . $tbl_dl . "`
						SET `categoryid`=" . $c . ", `name`='" . $n . "', `description`='" . $d . "', `version`='" . $v . "', `file`='".$f."', `release_notes`='".$rn."', `thumbnail`='".$tn."', `disabled`=".$dis."
						WHERE `downloadid`=" . $did . ";";
			}
			$db->query($sql);
		}
	}
	
	function removeDownload($downloadid)
	{
		global $db;
		$tbl_dl = MYSQL_TABLE_PREFIX . 'media_downloads';
		$d = (int)$downloadid;
		
		$dl = getDownload($d);
		$cat = getCategory($dl['categoryid']);
		$path = 'media/download/'.$cat['uniqid'].'/'.$dl['file'];
		if (file_exists($path))
			@unlink($path);
		
		$db->delete($tbl_dl, "`downloadid`=" . $d);
	}
	
	function listDownloads($categoryid)
	{
		global $db;
		$tbl_dl = MYSQL_TABLE_PREFIX . 'media_downloads';
		$c = (int)$categoryid;
		
		$list = $db->selectList($tbl_dl, "*", "`categoryid`=" . $c, "`name` ASC");
		if (count($list) > 0)
			foreach($list as $i => $l)
			{
				$list[$i]['description'] = cutString($l['description']);
				$list[$i]['url'] = makeURL('media', array('categoryid' => $c, 'downloadid' => $l['downloadid']));
			}
			
		return $list;
	}
	
	function increaseDownloadCounter($downloadid)
	{
		global $db;
		$dlid = (int)$downloadid;
		$db->insert('media_downloads_counter',
			array('downloadid', 'timestamp'),
			array($dlid, time())
		);
		$db->update('media_downloads', '`counter`=`counter`+1', '`downloadid`='.$dlid);
	}
	
	function getDownloadCounter($start_ts, $end_ts, $downloadid = 0) {
		global $db;
		$dlid = (int)$downloadid;
		if ($dlid > 0)
			return $db->num_rows('media_downloads_counter', '`timestamp` > '.(int)$start_ts.' AND `timestamp` < '.(int)$end_ts.' AND `downloadid`='.$dlid);
		else
			return $db->num_rows('media_downloads_counter', '`timestamp` > '.(int)$start_ts.' AND `timestamp` < '.(int)$end_ts);
	}
	
	function countDownloads($categoryids)
	{
		global $db;
		$tbl_dl = MYSQL_TABLE_PREFIX . 'media_downloads';
		$c = "(".$categoryids.")";
		
		$count = $db->num_rows($tbl_dl, "`categoryid` IN" . $c);
		return $count;
	}
	
	function getDownload($downloadid)
	{
		global $db, $user;
		$tbl_dl = MYSQL_TABLE_PREFIX . 'media_downloads';
		$dlid = (int)$downloadid;
		
		$result = $db->selectOneRow($tbl_dl, "*", "`downloadid`=" . $dlid);
		$u = $user->getUserById($result['userid']);
		$result['nickname'] = $u['nickname'];
		$result['counter'] = $db->num_rows('media_downloads_counter', '`downloadid`='.$dlid);
		return $result;
	}
	
	function addPictures($categoryid)
	{
		global $db, $login, $config;
		$c = (int)$categoryid;
		$category = getCategory($c);
		$folder = $category['uniqid'];
		
		$path = 'media/images/'.$folder.'/';
		@mkdir($path, 0777);
		@chmod($path, 0777);
		
		$upload = new Upload();
		$upload->dir = $path;
		if ($config->get('media', 'max-upload-size') > 0)
			$upload->max_byte_size = $config->get('media', 'max-upload-size');
		else
			$upload->max_byte_size = 10485760;
		$result = $upload->uploadArray();
		
		// Resize image if too large
		if ($config->get('media', 'auto-resize') != 0) {
			$max_width = (int)$config->get('media', 'auto-resize-width');
			if ($max_width == 0)
				$max_width = 1024;
			
			$image = new SimpleImage();
			
			for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
				if (file_exists($path.$_FILES['file']['name'][$i])) {
					
					$image->load($path.$_FILES['file']['name'][$i]);
					if ($image->getWidth() > $max_width) {
						$image->resizeToWidth($max_width);
						unlink($path.$_FILES['file']['name'][$i]);
						$image->save($path.$_FILES['file']['name'][$i]);
					}

				}
			}
		}
	}
	
	function listPictures($categoryid)
	{
		global $db;
		if ($categoryid == 0)
			return;
		
		$c = getCategory((int)$categoryid);
		return getImages($c['uniqid']);
	}
	
	function getImages($folder)
	{
		global $config;
		$ppr = (int)$config->get('media', 'pictures-per-row');
		$path = 'media/images/' . $folder;
		@$list = scandir($path);
		
		$img = array();
		if (count($list) > 2)
		{
			$i = 0;
			$j = 0;
			foreach ($list as $l)
			{
				if ($l != '.' && $l != '..')
				{
					$img[$i][$j] = $l;
					$j++;
					if ($j >= $ppr)
					{
						$j = 0;
						$i++;
					}
				}
			}
		}
		
		return $img;
	}
	
	function addMovie($categoryid, $name, $file, $description = "")
	{
		global $db, $login;
		$tbl_mov = MYSQL_TABLE_PREFIX . 'media_movies';
		$c = (int)$categoryid;
		$n = secureMySQL($name);
		$f = secureMySQL($file);
		$d = secureMySQL($description);
		
		$sql = "INSERT INTO `" . $tbl_mov . "` 
					(`categoryid`, `name`, `file`, `userid`, `timestamp`, `description`)
					VALUES
					(" . $c . ", '" . $n . "', '" . $f . "', " . $login->currentUserID() . ", " . time() . ", '" . $d . "');";
					
		$db->query($sql);
		
		return mysql_insert_id();
	}
	
	function listMovies($categoryid)
	{
		global $db;
		$tbl_mov = MYSQL_TABLE_PREFIX . 'media_movies';
		$c = (int)$categoryid;
		
		$list = $db->selectList($tbl_mov, "*", "`categoryid`=" . $c, "`name` ASC");
		
		if (count($list) > 0)
			foreach ($list as $i => $l)
			{
				$list[$i]['url'] = makeURL('media', array('categoryid' => $categoryid, 'movieid' => $l['movieid']));
				$list[$i]['description'] = cutString($l['description']);
			}
		return $list;
	}
	
	function editMovie($movieid, $name, $file, $description)
	{
		global $db;
		$tbl_mov = MYSQL_TABLE_PREFIX . 'media_movies';
		$m = (int)$movieid;
		$n = secureMySQL($name);
		$f = secureMySQL($file);
		$d = secureMySQL($description);
		
		$sql = "UPDATE `" . $tbl_mov . "`
					SET `name`='" . $n . "', `file`='" . $f . "', `description`='" . $d . "'
					WHERE `movieid`=" . $m . ";";
		$db->query($sql);
		
	}
	
	function removeMovie($movieid)
	{
		global $db;
		$tbl_mov = MYSQL_TABLE_PREFIX . 'media_movies';
		$m = (int)$movieid;
		
		$db->delete($tbl_mov, "`movieid`=" . $m);
	}
	
	function countMovies($categoryids)
	{
		global $db;
		$tbl_mov = MYSQL_TABLE_PREFIX . 'media_movies';
		$c = "(".$categoryids.")";
		return $db->num_rows($tbl_mov, "`categoryid` IN " . $c);
	}
	
	function getMovie($movieid)
	{
		global $db;
		$tbl_mov = MYSQL_TABLE_PREFIX . 'media_movies';
		return $db->selectOneRow($tbl_mov, "*", "`movieid`=" . (int)$movieid);
		
	}
	
	function listImageFolders()
	{
		$list = scandir("./media/images/");
		$l2 = '';
		if (count($list) > 0)
		foreach($list as $l)
		{
			if (substr($l, 0, 1) != '.')
				$l2[] = $l;
		}
		return $l2;
	}
	
	function listAvailableDownloads()
	{
		$list = scandir("./media/download/");
		$l2 = '';
		if (count($list) > 0)
		foreach($list as $l)
		{
			if (substr($l, 0, 1) != '.')
				$l2[] = $l;
		}
		return $l2;
	}
	
	function listAvailableMovies()
	{
		$list = scandir("./media/movie/");
		if (count($list) > 0)
		foreach($list as $l)
		{
			if (substr($l, 0, 1) != '.' && substr($l, strlen($l) - 4, 4) == '.flv')
				$l2[] = $l;
		}
		return @$l2;
	}
	
	function isVisible($categoryid)
	{
		global $db, $login, $rights;
		
		if ($categoryid == 0)
			return true;
		
		if ($db->num_rows('media_categories_permissions', '`categoryid`='.(int)$categoryid) == 0)
			return true;
		
		$groups = array_row($rights->getGroups($login->currentUserID()), 'groupid');
		if (count($groups) > 0) {
			if ($db->num_rows('media_categories_permissions', '`categoryid`='.(int)$categoryid.' AND `groupid` IN ('.implode(', ', $groups).')') > 0)
				return true;
		}
		return false;
	}
	
	function getTopDownloads() {
		global $db, $login, $rights, $bbcode, $current_language;
		$groups = $rights->getGroups($login->currentUserId());
		if(count($groups) > 0) {
			$groupswhere = "IN (-1,";
			for($i=0; $i<count($groups)-1; $i++) {
				$groupswhere .=$groups[$i]['groupid'].",";
			}
			$groupswhere.=$groups[count($groups)-1]['groupid'].")";
			
		} else {
			$groupswhere = " = -1 ";
		}
		$tbl_dl = MYSQL_TABLE_PREFIX . 'media_downloads';
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$tbl_perm = MYSQL_TABLE_PREFIX . 'media_categories_permissions';
		
		$items = array();
		$result = $db->query("
			SELECT dl.categoryid, dl.name, dl.downloadid, dl.description, perm.groupid, cat.language FROM ".$tbl_dl." AS dl 
			LEFT JOIN ".$tbl_perm." AS perm ON dl.categoryid = perm.categoryid
			LEFT JOIN ".$tbl_cat." AS cat ON cat.categoryid = dl.categoryid
			WHERE IFNULL(perm.groupid, -1) ".$groupswhere." 
			AND (cat.language = '' OR cat.language = '".$current_language."')
			GROUP BY dl.downloadid
			ORDER BY counter DESC 
			LIMIT 10;"
		);
		
		while ($row = mysql_fetch_assoc($result)) {
			$row['url'] = makeURL('media', array('categoryid' => $row['categoryid'], 'downloadid' => $row['downloadid']));
			$row['description'] = $bbcode->parse($row['description']);
			$items[] = $row;
		}
		return $items;
	}
	
	function getNewestDownloads() {
		global $db, $login, $rights, $current_language;
		$groups = $rights->getGroups($login->currentUserId());
		if(count($groups) > 0) {
			$groupswhere = "IN (-1,";
			for($i=0; $i< count($groups)-1; $i++) {
				$groupswhere .=$groups[$i]['groupid'].",";
			}
			$groupswhere.=$groups[count($groups)-1]['groupid'].")";
			
		} else {
			$groupswhere = " = -1";
		}
		$tbl_dl = MYSQL_TABLE_PREFIX . 'media_downloads';
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$tbl_perm = MYSQL_TABLE_PREFIX . 'media_categories_permissions';
		
		$items = array();
		$result = $db->query("
			SELECT timestamp, dl.categoryid, dl.name, dl.downloadid, dl.description, perm.groupid FROM ".$tbl_dl." AS dl 
			LEFT JOIN ".$tbl_perm." AS perm ON dl.categoryid = perm.categoryid
			LEFT JOIN ".$tbl_cat." AS cat ON cat.categoryid = dl.categoryid
			WHERE IFNULL(perm.groupid, -1) ".$groupswhere." 
			AND (cat.language = '' OR cat.language = '".$current_language."')
			GROUP BY dl.downloadid
			ORDER BY timestamp DESC 
			LIMIT 10;"
		);
		
		while ($row = mysql_fetch_assoc($result)) {
			$row['time'] = timeElapsed($row['timestamp']);
			$row['url'] = makeURL('media', array('categoryid' => $row['categoryid'], 'downloadid' => $row['downloadid']));
			$items[] = $row;
		}
		return $items;
	}
	
	function createMediaTreeXml($usermail, $password, $download_downloads=true, $download_images=true, $download_movies=false){
		global $db;
				
		$tbl_users = MYSQL_TABLE_PREFIX . 'users';
		$tbl_group_users = MYSQL_TABLE_PREFIX . 'group_users';
		
		if (trim($usermail) == '' and trim($password) == ''){
			return false;
		} else {
			$user = $db->selectOneRow($tbl_users, "*", "`email`='".secureMySQL($usermail)."' AND `password`='".generatePasswordHash($password)."'");
				
			if(!$user) {
				return false;
			}
			
			$groups = $db->selectList($tbl_group_users, "*", "`userid`=".$user['userid']);
			$assigned_groups[] = "0";
			if(count($groups)>0) {
				foreach($groups as $group) {
					$assigned_groups[] = $group['groupid'];
				}
			}
		} 
		
		$tbl_downloads = MYSQL_TABLE_PREFIX . 'media_downloads';
		$tbl_images = MYSQL_TABLE_PREFIX . 'media_images';
		$tbl_movies = MYSQL_TABLE_PREFIX . 'media_movies';
		
		$categories = createCatTree($assigned_groups);
		
		$downloadlist = array(); 
		
		if($download_downloads) {
			$downloads = $db->selectList($tbl_downloads, "*");
			foreach ($downloads as $download) {
				if(@$categories[$download['categoryid']]) {
					$downloadlist[] = array('downloadid' => $download['downloadid'], 
										'name'=> str_replace("&", "&amp;", $download['file']), 
										'path' => $categories[$download['categoryid']]['path'],
										'path_internal' => "media/download/".$categories[$download['categoryid']]['path_internal'],
										'filesize' => filesize("media/download/".$categories[$download['categoryid']]['path_internal']."/".$download['file']),
										'hash' => sha1_file("media/download/".$categories[$download['categoryid']]['path_internal']."/".$download['file']));
				}
			}
		}
		
		if($download_images) {
			$img_categories = scandir("media/images");
			foreach($img_categories as $img_category) {
				if(@$categories[$img_category]) {
					$images = scandir("media/images/".$img_category);
					foreach($images as $image) {
						if($image != ".." && $image != ".") {
							$downloadlist[] = array('downloadid' => $img_category, 
										'name'=> str_replace("&", "&amp;", $image), 
										'path' => $categories[$img_category]['path'],
										'path_internal' => "media/images/".$categories[$img_category]['path_internal'],
										'filesize' => filesize("media/images/".$categories[$img_category]['path_internal']."/".$image),
										'hash' => sha1_file("media/images/".$categories[$img_category]['path_internal']."/".$image));
						}
					}
				}
			}
		}
		
		if($download_movies) {
			$mov_categories = scandir("media/movie");
			foreach($mov_categories as $mov_category) {
				if(@$categories[$mov_category]) {
					$movies = scandir("media/movie/".$mov_category);
					foreach($movies as $movie) {
						if($movie != ".." && $movie != ".") {
							$downloadlist[] = array('downloadid' => $mov_category, 
										'name'=> str_replace("&", "&amp;", $movie), 
										'path' => $categories[$mov_category]['path'],
										'path_internal' => "media/movie/".$categories[$mov_category]['path_internal'],
										'filesize' => filesize("media/movie/".$categories[$mov_category]['path_internal']."/".$movie),
										'hash' => sha1_file("media/movie/".$categories[$mov_category]['path_internal']."/".$movie));
						}
					}
				}
			}
		}
		
		return $downloadlist;
	}
	
	function createCatTree($assigned_groups) {
		global $db;
		$tbl_cat = MYSQL_TABLE_PREFIX . 'media_categories';
		$list = $db->selectList($tbl_cat, "*", "`assigned_groupid` IN (".implode($assigned_groups,",").")", "`parentid` ASC");
		$categories = array();
		
		foreach($list as $category) {
			$curCategory = $category;
			$path = str_replace("\\", "-",  str_replace("/", "-", $category['name']));
			while($category['parentid'] != 0) {
				$category = $db->selectOneRow($tbl_cat, "*", "`categoryid`=".$category['parentid']);
				$path = str_replace("\\", "-", str_replace("/", "-", $category['name']))."\\".$path;
				if(!in_array($category['assigned_groupid'], $assigned_groups)) {
					break 2;
				}
			}
			$categories[$curCategory['categoryid']] = array('categoryid' => $curCategory['categoryid'], 
															'name' => str_replace("\\", "-", str_replace("/", "-", $curCategory['name'])), 
															'path' => "\\".$path, 
															'path_internal' => $curCategory['categoryid']);
		}
		return $categories;
	}
	
?>