<?php

class promote extends expunge
{
	public function __default()
	{
		// Inserts the post into the database as a draft
		$data = array(
			'post_author'       => '1',
			'post_date'         => date('Y-m-d H:i:s'),
			'post_date_gmt'     => gmdate('Y-m-d H:i:s'),
			'post_title'        => $_REQUEST['title'],
			'post_status'       => 'draft',
			'post_name'         => str_replace(' ' , '-', strtolower($_REQUEST['title'])),
			'post_modified'     => date('Y-m-d H:i:s'),
			'post_modified_gmt' => gmdate('Y-m-d H:i:s')
		);

		$id = $this->db->insert('wp_posts', $data);
		
		// Finds the image and extract the extension
		$path  = getcwd() . '/../incoming/' . $_REQUEST['id'] . '/';
		$files = scandir($path);
		
		foreach ($files as $file)
		{
			if ($file != '.' && $file != '..')
			{
				$filename  = $path . $file;
				$parts     = explode('.', $file);
				end($parts);
				$extension = current($parts);
			}
		}

		// Creates the directory for the image and moves the original
		$public_path = '/submissions/' . date('Y/m/') . $id . '/';
		$new_path    = getcwd() . $public_path;
		$original    = $new_path . 'original' . $extension;
		mkdir($new_path, 0777, true);
		copy($filename, $original);

		// Scales the image down to 500px wide
		$thumb = new Imagick($original);
		$thumb->thumbnailImage(500, 500, true);
		$thumb->writeImage($new_path . 'scaled_500.' . $extension);

		// Updates the post content and marks it as published
		$data = array(
			'post_content' => '<img src="http://images.parkmuch.com' . $public_path . 'scaled_500.' . $extension . '" /><br /><br />' . $_REQUEST['content'],
			'post_status'  => 'publish',
			'guid'         => 'http://parkmuch.com/?p=' . $id
		);
		$this->db->update('wp_posts', $data, array('ID' => $id));

		// Expunges the data
		parent::__default();
	}
}

?>
