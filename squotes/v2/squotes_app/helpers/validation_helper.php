<?php

if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

if (! function_exists ( 'validate_date' )) {
	function validate_date($date, $format = 'Y-m-d H:i:s')
	{
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}	
}

if (! function_exists ( 'validate_int' )) {
	function validate_int($value)
	{
		if (!is_numeric($value) || !is_int(0+$value))
			return false;
		else
			return true;
	}
}