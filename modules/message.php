<?php

class message extends admin
{
	public function __default()
	{
		$message  = $this->db->getRow('SELECT * FROM incoming WHERE id = "' . $_REQUEST['id'] . '";');
		$path     = getcwd() . '/../incoming/' . $_REQUEST['id'] . '/';
		$files    = scandir($path);
		
		foreach ($files as $file)
		{
			if ($file != '.' && $file != '..')
			{
				$filename = $path . $file;
			}
		}
		
		$size = @getimagesize($filename);

		$message['attachment'] = $files[2];
		$message['details']    = $size;

		$this->setPublic('message', $message);
	}
}

?>
