<input type="hidden" name="host" id="host" value="<?php echo $_GET['host']; ?>" />
<input type="hidden" name="user" id="user" value="<?php echo $_GET['user']; ?>" />
<input type="hidden" name="password" id="password" value="<?php echo $_GET['password']; ?>" />
<input type="hidden" name="database" id="database" value="<?php echo $_GET['db']; ?>" />

<?php
	
	$c = @mysqli_connect($_GET['host'], $_GET['user'], $_GET['password']);
	if ($c)
	{
		$d = @mysqli_select_db($_GET['db'], $c);
		if ($d)
		{	
			$result = mysqli_query( $c, "SELECT * FROM `".$_GET['db']."`.`tbl_users`") or die(((is_object($c)) ? mysqli_error($c) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)));
			echo '<p><strong>' . mysqli_num_rows($result) . '</strong> Users found.</p>
				<p><input type="button" name="do_import" value="Import users now" onClick="javascript:import();" /></p>';
		}
		else
			echo '<font color="#AA0000">Database not found.</font>';
	}
	else
		echo '<font color="#AA0000">Connection to database server failed.</font>';
	
	@mysqli_close($c);
?>