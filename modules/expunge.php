<?php

class expunge extends admin
{
	public function __default()
	{
		$this->db->execute('DELETE FROM incoming WHERE id = "' . $_REQUEST['id'] . '";');
		
		$path     = getcwd() . '/../incoming/' . $_REQUEST['id'] . '/';
		$files    = scandir($path);
		
		foreach ($files as $file)
		{
			if ($file != '.' && $file != '..')
			{
				unlink($path . $file);
			}
		}

		rmdir($path);

		header('Location: /admin');
	}
}

?>
