<?php

function formatMysqlDateTimeToDateTimeLocal($datetimeFromMysql) {
	$time = strtotime($datetimeFromMysql);
	$dateTimeLocal = date("Y-m-d", $time)."T".date("H:i", $time);
	return $dateTimeLocal;
}


?>