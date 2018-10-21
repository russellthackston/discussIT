<?php

class RegistrationCode {
	public $registrationcode;
	public $starttime;
	public $endtime;

	function __construct($registrationcode, $starttime, $endtime) {
		if (empty($registrationcode)) {
			throw new Exception('Registration code cannot be empty');
		}
		if (empty($starttime)) {
			throw new Exception('Start time cannot be empty');
		}
		if (empty($endtime)) {
			throw new Exception('End time cannot be empty');
		}
		$this->registrationcode = $registrationcode;
		$this->starttime = $starttime;
		$this->endtime = $endtime;
   	}

   	public static function fromArray($ary) {
	   	return new RegistrationCode($ary['registrationcode'], $ary['starttime'], $ary['endtime']);
    }

   	public static function listFromArray($ary) {
	   	$list = array();
	    for ($i = 0; $i < sizeof($ary); $i++) {
		    $list[] = RegistrationCode::fromArray($ary[$i]);
		}
		return $list;
    }

}

?>