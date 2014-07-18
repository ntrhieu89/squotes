<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Hieu Nguyen
 */

require APPPATH.'/libraries/REST_Controller.php';

/**
 * Admin controller
 * @author Hieu
 *
 */
class Error extends REST_Controller {
	function __construct() {
		parent::__construct();
	}
	
	function index_get() {
		$resp = array(
			'status' => 404,
			'message' => 'The API you are looking for does not exist. Please check the URL carefully.',
			'data' => null
		);
		
		$this->response($resp, 404);
	}
}