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
class BASE_Controller extends REST_Controller {
	function __construct() {
		parent::__construct();
		
		$this->load->model('user_model');
	}
	
	/**
	 * Prepares response
	 * @return multitype:number string NULL
	 */
	function prepare_response() {
		return array(
				'status' => 200,
				'message' => 'Request has been processed successfully',
				'data' => null
		);
	}
	
	function get_validation_errors($error_array) {
		$str = '';
	
		foreach ($error_array as $key => $err) {
			$str = $str.$err;
		}
		
		if ($str == '')
			$str = 'Some validation errors occur.';
	
		return $str;
	}
	
	/**
	 * Authorize admin (input code variable should be 'Ap$tick')
	 * @param unknown $code
	 * @return boolean
	 */
	function authorize_admin() {		
		$code = $this->post('admin_access_token');
		
		if ($code == false)
			$code = $this->get('admin_access_token');
		
		if ($code == false)
			return false;
	
		$salt = '26bfabe84c4963c96e1150e6bcfb41675d3dc6862545f896a387eea4dae7ab4b340a875c19f416dea9938e1e7178dd6775fec0d8a358b0921dea267bc803408b';
		$true_code = '6e9fb802ff14a0b5af86e3b521a2e9c87e56ecec6ed100d092099385e2e202a9dbb5ba51e3f7b8accbb9ffdc6e48370dc32920edce77b1a0674a1f00be915e34';
	
		// hash client code
		$code_hash = hash('sha512', $code . $salt);
	
		if ($code_hash === $true_code)
			return true;
		else
			return false;
	}
	
	/**
	 * Checks authentication of user.
	 * If user found, return the userid.
	 * If access token is invalid, response error
	 * If access token is not provided, return false
	 */
	function check_authentication() {
		// check access_token if existed
		$access_token = $this->get('access_token');
	
		if ($access_token == false)
			$access_token = $this->post('access_token');
	
		if ($access_token != false) {
			$userid = $this->user_model->check_access_token($access_token);
			if ($userid == false) {
				$resp['status'] = 401;
				$resp['message'] = 'Access_token provided is not valid.';
				$this->response($resp, 401);
			} else {
				return $userid;
			}
		} else {
			return false;
		}
	}
}