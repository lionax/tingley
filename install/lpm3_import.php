<?php

	// connect to lpm3 db
	$c = @mysqli_connect($_GET['host'], $_GET['user'], $_GET['password']);
	if ($c)
	{
		$d = @mysqli_select_db($_GET['db'], $c);
		if ($d)
		{
			
			// connect to hfh db
			require_once('../config/database.config.php');
			$hfh_connection = @mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD);
			if ($hfh_connection)
			{
				$hfh_db = ((bool)mysqli_query($GLOBALS["___mysqli_ston"], "USE " . constant('MYSQL_DATABASE')));
				if ($hfh_db)
				{
					// list old users
					$result = mysqli_query( $c, "SELECT * FROM `".$_GET['db']."`.`tbl_users`") or die(((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
					while ($row = mysqli_fetch_assoc($result))
					{
						// import user
						$bd = mktime(0, 0, 0, $row['month'], $row['day'], $row['year']);
						$sql = "INSERT INTO `".MYSQL_DATABASE."`.`".MYSQL_TABLE_PREFIX."users`
								(`email`, `password`, `nickname`, `prename`, `lastname`, `birthday`, `ban`, `activated`, `comment`)
								VALUES
								('".$row['email']."', '".$row['pswd']."', '".$row['nick']."', '".$row['prename']."', '".$row['lastname']."', ".(int)$bd.", ".(int)$row['banned'].", 1, '".$row['comment']."');";
						mysqli_query( $hfh_connection, $sql) or die(((is_object($hfh_connection)) ? mysqli_error($hfh_connection) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
						
					}
					
					// done
					echo '<strong>'.mysqli_num_rows($result).'</strong> users successfully imported. Click <em>Next</em> to proceed.';
				}
			}
		}
		else
			echo '<font color="#AA0000">Database not found.</font>';
	}
	else
		echo '<font color="#AA0000">Connection to database server failed.</font>';
	
	@mysqli_close($c);
	@mysqli_close($hfh_connection);
?>