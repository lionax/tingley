<?php
	$connection = @mysqli_connect($_GET['host'], $_GET['user'], $_GET['password']);
	if (!$connection)
	{
		echo '<font color="#AA0000">Connection to database server failed.</font>';
	}
	else {
		$db = $connection->select_db($_GET['db']);
		if (!$db) {
			echo '<font color="#AA0000">Database not found.</font>';
		}
		else {
			echo '<font color="#00AA00">Connection to database server successfully established.</font>';
			echo '<script type="text/javascript">$("#next").removeAttr("disabled");</script>';
		}

		// connection established and will now be closed
		$connection->close();
	}
?>