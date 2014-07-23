<?php

if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

if (! function_exists ( 'convert_date' )) {
	function time_to_unixtimestamp($time, $zone) {
		if ($time == false)
			return false;	
		
		$schedule_time = new DateTime($time, new DateTimeZone($zone) );
		$schedule_time->setTimeZone(new DateTimeZone('UTC'));
		return strtotime($schedule_time->format('Y-m-d H:i:s'));
	}
}