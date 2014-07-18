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
class User extends REST_Controller {
	function __construct() {
		parent::__construct();
	
		// loading the database model
		$this->load->model('user_model');
		$this->load->model('quote_model');
		
		// form validation
		$this->load->library('form_validation');
		$this->load->helper('validation_helper');
	}
	
	/**
	 * Gets general info about user
	 * @param unknown $user
	 * @return boolean|multitype:unknown
	 */
	private function _get_info($user) {
		if ($user == false)
			return false;
		
		$this->load->helper('converter_helper');
		$info = array(
			'userid' => $user['userid'],
			'email' => $user['email'],
			'firstname' => $user['firstname'],
			'lastname' => $user['lastname'],
			'avatar' => $user['avatar'],
			'jointime' => time_to_unixtimestamp($user['jointime'], 'America/Los_Angeles'),
		);
		
		return $info;
	}
	
	/**
	 * POST /user
	 * Create a new user, then sign in automatically
	 */
	public function index_post() {		
		// response data
		$resp = array(
			'status' => 200,
			'message'=> 'User created successfully.',
			'data' => null
		);
		
		// check validation
		if ($this->form_validation->run('signup') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->_get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 400);
		}
		
		$email = $this->post('email');
		$password = $this->post('password');
		$firstname = $this->post('firstname');
		$lastname = $this->post('lastname') == false ? '' : $this->post('lastname');
		
		if ($this->user_model->create_user($email, $password, $firstname, $lastname) == TRUE) {
			$id = mysql_insert_id();
			
			// get user
			$user = $this->user_model->get_user_by_id($id);
			
			// user not found
			if ($user === null) {
				$resp['status'] = 500;
				$resp['message'] = 'User created but information cannot be retrieved';
				$this->response($resp, 500);				
			}
			
			// create token
			$token = $this->user_model->generate_token($user['userid']);
			
			if ($token == false) {
				$resp['status'] = 500;
				$resp['message'] = 'Cannot sign in. Try again later';				
				$this->response($resp, 500);	// this potentially is a server error
			}
			
			$data = array(
				'access_token' => $token,
				'user' => $this->_get_info($user),
			);
			$resp['data'] = $data;
			
			$this->response($resp, 200);
		} else {
			$resp['status'] = 500;
			$resp['message'] = 'Some unclear server errors happen.';
			$this->response($resp, 500);		// HTTP code 500 for server error
		}
	}
	
	/**
	 * GET /user
	 * Get general info of a user
	 */
	public function index_get() {
		$resp = array(
			'status' => 200,
			'message' => 'User info is retrieved successfully.',
			'data' => null
		);
		
		// check input
		$userid = $this->get('userid');		
		if (validate_int($userid) == false) {
			$resp['status'] = 400;
			$resp['message'] = 'Parameter missing or invalid.';
			$this->response($resp, 400);
		}
		
		// get user info
		$user = $this->user_model->get_user_by_id($userid);		
		if ($user == false) {
			$resp['status'] = 404;
			$resp['message'] = 'User info not found.';
			$this->response($resp, 404);
		}

		// return data
		$resp['data'] = $this->_get_info($user);
		$this->response($resp, 200);
	}
	
	/**
	 * GET /user/access_token
	 * Sign in
	 */
	public function access_token_get() {
		$resp = array(
			'status' => 200,
			'message' => 'Access_token is retrieved successfully.',
			'data' => null
		);
		
		$email = $this->get('email');
		$password = $this->get('password');		
		if ($email == false || $password == false) {
			$resp['status'] = 400;
			$resp['message'] = 'Email or password is missing.';
			$this->response($resp, 400);
		}
		
		$user = $this->user_model->check_credentials($email, $password);
		if ($user == false) {
			$resp['status'] = 401;
			$resp['message'] = 'User not found or password is invalid.';
			$this->response($resp, 401);
		} else {
			// generate token
			$token = $this->user_model->generate_token($user['userid']);
			if ($token == false) {
				$resp['status'] = 500;
				$resp['message'] = 'Cannot sign in. Try again later';
				$this->response($resp, 500);	// this potentially is a server error
			}
			
			$info = $this->_get_info($user);
			$resp['data'] = array(
				'access_token' => $token,
				'user' => $info,
			);
			
			$this->response($resp, 200);
		}		
	}
	
	/**
	 * DELETE user/access_token
	 * Sign out
	 */
	public function access_token_post() {
		$resp = array(
			'status' => 200,
			'message' => 'Access_token has been deleted.',
			'data' => null	
		);
		
		$token = $this->post('access_token');
		
		if ($token == false) {
			$resp['status'] = 401;
			$resp['message'] = 'Access_token input is required.';
			$this->response($resp, 401);
		}
		
		if ($this->user_model->expire_token($token))
			$this->response($resp, 200);
		else {
			$resp['status'] = 401;
			$resp['message'] = 'Access_token is not valid.';
			$this->response($resp, 401);
		}
	}
	
	private function _get_validation_errors($error_array) {
		$str = '';
	
		foreach ($error_array as $key => $err) {
			$str = $str.$err;
		}
	
		return $str;
	}
}