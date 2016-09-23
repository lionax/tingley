<?php
	
	function content($s)
	{
		global $db, $lang;
		$tbl = MYSQL_TABLE_PREFIX . 'content';
		$return = array();
		
		$result = $db->queryToList("SELECT * FROM `".$tbl."` 
									WHERE `title` LIKE '%" . $s . "%' OR
										`text` LIKE '%" . $s . "%' OR
										`box_content` LIKE '%" . $s . "%'
									GROUP BY `key` 
									ORDER BY `version` DESC");
		
		if (count($result) > 0)
		foreach ($result as $i => $r)
		{
			$engine = $lang->get('engines_content');
			$title = cutString($r['title']);
			$description = $r['text'];
			$url = makeURL($r['key']);
			$relevance = strcount($r['text'].' '.$r['title'].' '.$r['title'].' '.$r['title'], $s);
			
			$return[] = array('engine' => $engine,
							  'title' => $title,
							  'description' => $description,
							  'url' => $url,
							  'relevance' => $relevance);
		}
		
		return $return;
	}

?>