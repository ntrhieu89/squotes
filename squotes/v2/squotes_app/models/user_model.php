<?php

/**
 * User model
 * @author Nguyen
 *
 */
class User_model extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	/**
	 * Checks if a username is in DB
	 *
	 * @access	public
	 * @param	string $username_or_email
	 * @return	TRUE if user already exists, otherwise FALSE
	 */	
	public function user_exists($username_or_email) {
		// find database for user with specified username or email
		$this->db->where('username', $username_or_email);
		$this->db->or_where('email', $username_or_email);
		$query = $this->db->get('users');
		
		if ($query != false && $query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Creates a new user
	 *
	 * @access	public
	 * @param	string $username the user's username
	 * @param	string $password the user's password
	 * @return	TRUE if user creation was successfull, otherwise FALSE
	 */	
	public function create_user($username, $password) {
		// Create a random salt
		$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), TRUE));
		
		// Create salted password (Careful not to over season)
		$pw = hash('sha512', $password . $random_salt);
		
		$user_data = array(
			'username' => $username,
			'password' => $pw,
			'email' => '',
			'firstname' => "",
			'lastname' => "",
			'avatar' => "",
			'salt' => $random_salt,
		);		
		
		$success = TRUE;
		
		// Transaction
		if (!$this->db->insert('users', $user_data))
			$success = FALSE;
		
		return $success;	
	}
	
	/**
	 * Sets avatar for user specified by user id
	 * @param unknown $userid
	 * @param unknown $avatar
	 * @return boolean
	 */
	public function set_avatar($userid, $avatar) {
		$data = array(
			'avatar' => $avatar	
		);
		
		$this->db->where('userid', $userid);
		$this->db->update('users', $data);

		if ($this->db->affected_rows() == 0 || $this->db->affected_rows() == 1) {
			return true;
		} else 
			return false;
	}
	
	/**
	 * Sets name for a user specified by user id
	 * @param unknown $userid
	 * @param unknown $firstname
	 * @param unknown $lastname
	 * @return boolean
	 */
	public function set_name($userid, $firstname, $lastname) {
		$data = array(
			'firstname' => $firstname,
			'lastname' => $lastname
		);
		
		$this->db->where('userid', $userid);
		$this->db->update('users', $data);
		
		if ($this->db->affected_rows() == 1)
			return true;
		else 
			return false;
	}
	
	/**
	 * Changes password
	 * @param unknown $userid
	 * @param unknown $oldpass
	 * @param unknown $newpass
	 * @return boolean
	 */
	public function change_password($userid, $oldpass, $newpass) {
		$this->db->where('userid', $userid);
		$result = $this->db->get('users');
		
		if ($result == true && $result->num_rows() == 1) {
			$user = get_object_vars($result->result()[0]);
			
			$pw = hash('sha512', $oldpass . $user['salt']);
			
			if ($pw === $user['password']) {
				$new_pw = hash('sha512', $newpass . $user['salt']);
				$data = array('password' => $new_pw);
				
				$this->db->where('userid', $userid);
				$this->db->update('users', $data);
				
				if ($this->db->affected_rows() == 1)
					return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Gets user information specified by user id
	 * @param unknown $userid
	 * @return multitype:|boolean
	 */
	public function get_user_by_id($userid) {
		$query = $this->db->query('select * from users where userid='.$userid.'');
		
		if ($query == true && $query->num_rows() == 1) {
			return get_object_vars($query->result()[0]);
		} else 
			return false;
	}
	
	public function get_user($username_or_email) {
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('username', $username_or_email);
		$this->db->or_where('email', $username_or_email);
		$this->db->limit(1);
	
		$query = $this->db->get();
	
		if ($query == true && $query->num_rows() == 1) {
			// Return the row
			return get_object_vars($query->result()[0]);
		} else {
			return false;
		}
	}
	
	public function check_credentials($username, $password) {
		$user = $this->get_user($username);
		
		if ($user == false)
			return false;

		$pw = hash('sha512', $password . $user['salt']);
		if ($pw === $user['password'])
			return $user;
		else
			return false;
	}
	
	/**
	 * Check if the access token is valid (belonging to an active user)
	 * @param unknown $access_token
	 */
	public function check_access_token($access_token) {
		$query = $this->db->query('select * from usertokens where token=\''.$access_token.'\'');
		
		if ($query == null || $query->num_rows() != 1)
			return false;
		else {
			$data = get_object_vars($query->result()[0]);
			return $data['userid'];	
		}			
	}
	
	/**
	 * Gets a token that is still working
	 * @param unknown $userid
	 * @return multitype:|boolean
	 */
	private function _get_token($userid) {
		$query = $this->db->query('select * from usertokens where userid='.$userid.' and isexpired=0');
		
		if ($query == true && $query->num_rows() == 1) {
			$data = get_object_vars($query->result()[0]);
			return $data['token'];
		} else 
			return false;		
	}
	
	/**
	 * Generates a unique token for a user for authentication
	 * @param unknown $userid
	 * @return boolean
	 */
	public function generate_token($userid) {		
		$this->db->trans_start();
		
		// try to get a token if existed
		$token = $this->_get_token($userid);
		
		if ($token != false)
			return $token;
		
		// there is no token existed
		// generate a new token
		$this->load->helper('uuid_helper');
		
		$data = array(
			'userid' => $userid,
			'token' => gen_uuid(),
			'isexpired' => false,
		);
		
		$success = true;
		if (!$this->db->insert('usertokens', $data))
			$success = false;

		// finish transaction and check for success insertion
		if ($this->db->trans_complete() && $success == true)
			return $data['token'];
		else 
			return false;
	}
	
	public function expire_token($token) {
		if ($token == false)
			return false;	
		
		$data = array( 'isexpired' => true);
		$this->db->where('token', $token);
		$this->db->update('usertokens', $data);

		if ($this->db->affected_rows() == 1)			
			return true;
		else
			return false;
	}
}