<?php

	require_once("mod/default/media/media.function.php");
	
	$enable_downloads = isset($_GET['syncdownloads']) && $_GET['syncdownloads']=='True';
	$enable_images = isset($_GET['syncimages']) && $_GET['syncimages']=='True';
	$enable_movies = isset($_GET['syncmovies']) && $_GET['syncmovies']=='True';
	
	$data = createMediaTreeXml($_GET['email'], $_GET['password'], $enable_downloads, $enable_images, $enable_movies);
	
	echo "<files>\n\r";
	if($data) {
		foreach($data as $element) {
			echo "\t<file name=\"".$element['name']."\" path=\"".$element['path']."\" internalpath=\"".$element['path_internal']."/".$element['name']."\" filesize=\"".$element['filesize']."\" hash=\"".$element['hash']."\" />\n\r";
		}
	}
	echo "</files>\n\r";
	
?>