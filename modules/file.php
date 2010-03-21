<?php

class file extends admin
{
	public function __default()
	{
		$path  = getcwd() . '/../incoming/' . $_REQUEST['id'] . '/';
		$files = scandir($path);
		$file  = $path . $files[2];

		header('Content-Type: ' . mime_content_type($file));
		$handle = fopen($file, 'rb');
		fpassthru($handle);
		exit();
	}
}

?>
