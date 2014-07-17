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
class Admin extends REST_Controller {
	function __construct() {
		parent::__construct();
	
		// loading the database model
		$this->load->model('admin_model');
		$this->load->model('quote_model');
		
		$this->load->library('form_validation');
	}
	
	/**
	 * Resets the database
	 */
	public function database_delete() {
		if ($this->_authorize_admin($this->delete('admin_access_code')) == false)
			$this->response(null, 400);
		
		$var = $this->admin_model->reset_database();
		
		if ($var['success'] === $var['total'])
			$this->response($var, 200);
		else 
			$this->response($var, 400);
	}
	
	/**
	 * Gets all data as a json document
	 */
	public function database_get() {
		// TODO
	}
	
	/**
	 * Adds a new Quote of a famous person
	 */
	public function quote_post() {
		$resp = null;
		
		if ($this->form_validation->run('admin_add_quote') == false) {
			$resp = array('error' => validation_errors());
			$this->response($resp, 400);
		}
		
		$admin_access_token = $this->post('admin_access_token');
		$author = $this->post('author');
		$content = $this->post('content');
		$language = $this->post('language');
		
		if ($this->quote_model->add_quote($content, $author, $language) == true) {
			$id = mysql_insert_id();
			$quote = $this->quote_model->get_quote($id);
			
			if ($quote == false) {
				$resp = array('error', 'Add quote success but then cannot retrieve the data');
				$this->response($resp, 404);
			}
			
			$this->response($quote, 200);
		} else {
			$resp = array('error', 'Cannot add quote. Maybe it is posted before. Try again later.');
			$this->response($resp, 500);
		}
	}
	
	/**
	 * Validates a quote, so it is ready to be published
	 */
	public function quote_validation_post() {
		$admin_access_token = $this->post('admin_access_token');
		$quoteid = $this->post('quoteid');
	}
	
	/**
	 * Invalidates a quote (used when recognizes that the quote is inappropriate)
	 */
	public function quote_invalidation_post() {
		$admin_access_token = $this->post('admin_access_token');
		$quoteid = $this->post('quoteid');		
	}
	
	/**
	 * Gets all quotes that have not been validated.
	 */
	public function quotes_get() {
		if ($this->_authorize_admin($this->get('admin_access_code')) == false)
			$this->response(null, 400);

		$quotes = $this->quote_model->get_unvalidated_quotes();
				
		$this->response($quotes, 200);
	}
	
	/**
	 * Validates a quote. This quote then is ready to be schedulte to publish
	 */
	public function validation_post() {
		if ($this->_authorize_admin($this->post('code')) == false)
			$this->response(null, 400);
		
		$quoteid = $this->post('quoteid');
		
		if ($this->quote_model->validate_quote($quoteid) == true)
			$this->response(null, 200);
		else 
			$this->response(null, 400);
	}
	
	/**
	 * Deletes a quote
	 */
	public function quote_delete() {
		if ($this->_authorize_admin($this->post('code')) == false)
			$this->response(null, 400);

		$quoteid = $this->post('quoteid');
		
		if ($quoteid == false)
			$this->response(null, 400);
		
		if ($this->quote_model->delete_quote($quoteid) == false)
			$this->response(null, 400);
		else
			$this->response(null, 200);
	}

	/**
	 * Authorize admin (input code variable should be 'Ap$tick')
	 * @param unknown $code
	 * @return boolean
	 */
	private function _authorize_admin($code) {
		if ($code == false)
			return false;
		
		$salt = '26bfabe84c4963c96e1150e6bcfb41675d3dc6862545f896a387eea4dae7ab4b340a875c19f416dea9938e1e7178dd6775fec0d8a358b0921dea267bc803408b';
		$true_code = 'f98b5157ffdfbfe454447cbadc18d7a0d6a33f9771ac2701f0039a4b038e189865b9e02f8d2de99c3a7470d551d2f6acc17e3231a4361b72f7018b38e29ce4ef';
		
		// hash client code
		$code_hash = hash('sha512', $code . $salt);
		
		if ($code_hash === $true_code)
			return true;
		else 
			return false;
	}
}