<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Hieu Nguyen
 */

require APPPATH.'/controllers/api/BASE_Controller.php';

define("UPLOAD_DIR", 'squotes_app/files/avatars/');

/**
 * Admin controller
 * @author Hieu
 *
 */
class User extends BASE_Controller {	
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
			'avatar' => $this->_get_avatar_link($user['avatar']),
			'jointime' => time_to_unixtimestamp($user['jointime'], 'America/Los_Angeles'),
		);
		
		$data = $this->user_model->get_user_stats($user['userid']);
		
		$info['likes'] = $data['likes'];
		$info['favorites'] = $data['favorites'];
		
		return $info;
	}
	
	private function _get_avatar_link($filename) {
		if ($filename == false || $filename == '')
			return '';
		
		$path = UPLOAD_DIR.$filename;
		
		$link = $this->config->base_url().$path;
		$changes = array('http://localhost');
		
		$link = str_replace($changes, 'https://apstick.com', $link);
		
		return $link;
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
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
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
	public function access_token_delete() {
		$resp = array(
				'status' => 200,
				'message' => 'Access_token has been deleted.',
				'data' => null
		);
	
		$token = $this->delete('access_token');
	
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
	
	/**
	 * Sets avatar
	 */
	public function avatar_post() {
		$resp = array(
			'status' => 200,
			'message' => 'Avatar has been set successfully',
			'data' => null
		);
		
		// check access_token if existed
		$userid = $this->check_authentication();
		if ($userid == false) {	// userid missing
			$resp['status'] = 400;
			$resp['message'] = 'User unauthorized.';
			$this->response($resp, 400);
		}

		// change the uploaded file name
		$data = $_FILES['avatar'];
	
		if ($data == false || $data['name'] == false)
			$this->response($data, 400);
	
		$file_name = $data['name'];
		$ext = pathinfo($file_name, PATHINFO_EXTENSION);
	
// 		if (preg_match("/\.(?i)(jpg|png|gif|bmp)/", $ext, $match) == false)
// 			$this->response($data, 400);
	
		$new_file_name = $userid.'_avt.'.pathinfo($file_name, PATHINFO_EXTENSION);
	
		// set upload directory
		$upload_file = UPLOAD_DIR.$new_file_name;
	
		// move upload file
		if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_file)) {
			// update database
			if ($this->user_model->set_avatar($userid, $new_file_name)) {
				$link = $this->_get_avatar_link($new_file_name);
				
				$resp['data'] = $link;
				$this->response($resp, 200);
			}
		}
	
		$this->response($resp, 500);
	}
	
	/**
	 * Sets alias. If new alias is identical with the old alias,
	 * this function will return false.
	 */
	public function name_post() {
		$resp = array(
			'status' => 200,
			'message' => 'Name has been changed successfully',
			'data' => null
		);
		
		// check access_token if existed
		$userid = $this->check_authentication();
		if ($userid == false) {	// userid missing
			$resp['status'] = 400;
			$resp['message'] = 'User unauthorized.';
			$this->response($resp, 400);
		}
		
		if ($this->form_validation->run('set_name') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 400);
		}		
	
		$firstname = $this->post('firstname');
		$lastname = $this->post('lastname');		
		
		if ($lastname == false)
			$lastname = '';
	
		if ($this->user_model->set_name($userid, $firstname, $lastname) == true) {
			$this->response($resp, 200);
		} else {
			$resp['status'] = 500;
			$resp['message'] = 'Some errors occur. Please try again later';	
			$this->response(null, 500);
		}
	}
	
	public function password_post() {
		$resp = array(
				'status' => 200,
				'message' => 'Password has been changed successfully',
				'data' => null
		);
		
		// check access_token if existed
		$userid = $this->check_authentication();
		if ($userid == false) {	// userid missing
			$resp['status'] = 400;
			$resp['message'] = 'User unauthorized.';
			$this->response($resp, 400);
		}
	
		if ($this->form_validation->run('change_password') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 400);				
		}
	
		$oldpass = $this->post('oldpass');
		$newpass = $this->post('newpass');
	
		if ($oldpass === $newpass) {
			$this->response($resp, 200);
		}
	
		if ($this->user_model->change_password($userid, $oldpass, $newpass) == true)
			$this->response($resp, 200);
		else {
			$resp['status'] = 500;
			$resp['message'] = 'Some errors occur. Please try again later';	
			$this->response(null, 500);
		}
	}	
}