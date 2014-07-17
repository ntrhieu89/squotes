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
	}
	
	/**
	 * Gets general info about user
	 * @param unknown $user
	 * @return boolean|multitype:unknown
	 */
	private function _get_info($user) {
		if ($user == false)
			return false;
		
		$info = array(
			'userid' => $user['userid'],
			'username' => $user['username'],
			'email' => $user['email'],
			'firstname' => $user['firstname'],
			'lastname' => $user['lastname'],
			'avatar' => $user['avatar'],
			'jointime' => $user['jointime'],
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
			$resp['message'] = $this->form_validation->error_array();
			$this->response($resp, 400);
		}
		
		$username = $this->post('username');
		$password = $this->post('password');
		
		if ($this->user_model->create_user($username, $password) == TRUE) {
			// get user
			$user = $this->user_model->get_user($username);
			
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
		if ($userid == false) {
			$resp['status'] = 400;
			$resp['message'] = 'Parameter missing.';
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
		
		$username = $this->get('username');
		$password = $this->get('password');		
		if ($username == false || $password == false) {
			$resp['status'] = 400;
			$resp['message'] = 'Username or password is missing.';
			$this->response($resp, 400);
		}
		
		$user = $this->user_model->check_credentials($username, $password);
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
	public function access_token_delete() {
		$resp = array(
			'status' => 200,
			'message' => 'Access_token has been deleted.',
			'data' => null	
		);
		
		$token = $this->get('access_token');
		
		if ($token == false)
			$this->response(null, 200);
		
		if ($this->user_model->expire_token($token))
			$this->response($resp, 200);
		else {
			$resp['status'] = 401;
			$resp['message'] = 'Access_token is not valid.';
			$this->response($resp, 401);
		}
	}
}