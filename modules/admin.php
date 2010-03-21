<?php

class admin extends Module
{	
	protected $authentication = true;

	public function __default() {
		$this->setPublic('messages', $this->db->getArray('SELECT * FROM incoming ORDER BY received_at'));
	}
}

?>
