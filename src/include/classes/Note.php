<?php

class Note {
	public $id;
	public $text;
	public $order;
	public $registrationcode;

	function __construct($id, $text, $order, $registrationcode) {
		if (empty($id)) {
			throw new Exception('Note ID cannot be empty');
		}
		if (empty($text)) {
			throw new Exception('Note text cannot be empty');
		}
		if (empty($order)) {
			throw new Exception('Note order cannot be empty');
		}
		if (empty($registrationcode)) {
			throw new Exception('Registration code cannot be empty');
		}
		$this->id = $id;
		$this->text = $text;
		$this->order = $order;
		$this->registrationcode = $registrationcode;
   	}

   	public static function fromArray($ary) {
	   	return new Note($ary['noteid'], $ary['notetext'], $ary['noteorder'], $ary['registrationcode']);
    }

   	public static function listFromArray($ary) {
	   	$list = array();
	    for ($i = 0; $i < sizeof($ary); $i++) {
		    $list[] = Note::fromArray($ary[$i]);
		}
		return $list;
    }
    
}

?>