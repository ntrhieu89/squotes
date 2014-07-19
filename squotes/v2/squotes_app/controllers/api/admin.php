<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Hieu Nguyen
 */

require APPPATH.'/controllers/api/BASE_Controller.php';

/**
 * Admin controller
 * @author Hieu
 *
 */
class Admin extends BASE_Controller {
	function __construct() {
		parent::__construct();
	
		// loading the database model
		$this->load->model('admin_model');
		$this->load->model('quote_model');
		
		$this->load->library('form_validation');
	}
	
	function codehash_get() {
		$resp = $this->prepare_response();

		$code = $this->get('code');
		
		$salt = '26bfabe84c4963c96e1150e6bcfb41675d3dc6862545f896a387eea4dae7ab4b340a875c19f416dea9938e1e7178dd6775fec0d8a358b0921dea267bc803408b';
		
		// hash client code
		if ($code != false) {
			$code_hash = hash('sha512', $code . $salt);
			$resp['data'] = $code_hash;			
		}
		
		$this->response($resp, 200);
	}
	
	/**
	 * Resets the database
	 */
	public function database_post() {
		$resp = $this->prepare_response();
		
		if ($this->authorize_admin() == false) {
			$resp['status'] = 400;
			$resp['message'] = "Admin unauthorized.";
			$this->response($resp, 400);
		}
		
		$var = $this->admin_model->reset_database();
		
		if ($var['success'] === $var['total'])
			$this->response($var, 200);
		else 
			$this->response($var, 400);
	}
	
	/**
	 * Adds a new Quote of a famous person
	 */
	public function quote_post() {
		$resp = $this->prepare_response();
		
		if ($this->form_validation->run('admin_add_quote') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 400);
		}
		
		if ($this->authorize_admin() == false) {
			$resp['status'] = 401;
			$resp['message'] = 'Admin unauthorized.';
			$this->response($resp, 401);			
		}		
		
		$author = $this->post('author');
		$content = $this->post('content');
		$language = $this->post('language');
		
		if ($this->quote_model->add_famous_quote($content, $author, $language) == true) {
			$id = mysql_insert_id();
			$quote = $this->quote_model->get_quote($id);
			
			if ($quote == false) {
				$resp = array('error', 'Add quote success but then cannot retrieve the data');
				$this->response($resp, 404);
			}
			
			$resp['data'] = $quote;
			$this->response($resp, 200);
		} else {
			$resp['status'] = 500;
			$resp['message'] = 'Cannot add quote. Maybe it is posted before. Try again later.';
			$this->response($resp, 500);
		}
	}
	
	/**
	 * Validates a quote, so it is ready to be published
	 */
	public function quote_publish_post() {
		$resp = $this->prepare_response();
		
		if ($this->authorize_admin() == false) {
			$resp['status'] = 401;
			$resp['message'] = 'Admin unauthorized.';
			$this->response($resp, 401);
		}
		
		$this->load->helper('validation_helper');
		$quoteid = $this->post('quoteid');
		if (validate_int($quoteid) == true && $this->quote_model->is_existed($quoteid)) {
			if ($this->quote_model->publish_quote($quoteid) == true) {
				$this->response($resp, 200);				
			} else {
				$resp['status'] = 500;
				$resp['message'] = 'Cannot publish this quote.';
				$this->response($resp, 500);				
			}
		} else {
			$resp['status'] = 400;
			$resp['message'] = 'Quote ID is invalid.';
			$this->response($resp, 400);
		}
	}
	
	/**
	 * Invalidates a quote (used when recognizes that the quote is inappropriate)
	 */
	public function quote_delete_post() {
		$resp = $this->prepare_response();
		
		if ($this->authorize_admin() == false) {
			$resp['status'] = 401;
			$resp['message'] = 'Admin unauthorized.';
			$this->response($resp, 401);
		}
		
		$this->load->helper('validation_helper');
		$quoteid = $this->post('quoteid');
		if (validate_int($quoteid) == true && $this->quote_model->is_existed($quoteid)) {
			if ($this->quote_model->delete_quote($quoteid) == true) {
				$this->response($resp, 200);				
			} else {
				$resp['status'] = 500;
				$resp['message'] = 'Cannot delete this quote.';
				$this->response($resp, 500);				
			}
		} else {
			$resp['status'] = 400;
			$resp['message'] = 'Quote ID is invalid.';
			$this->response($resp, 400);
		}	
	}
	
	/**
	 * Gets all quotes that have not been validated.
	 */
	public function quotes_get() {
		$resp = $this->prepare_response();
		
		if ($this->authorize_admin() == false) {
			$resp['status'] = 401;
			$resp['message'] = 'Admin unauthorized.';
			$this->response($resp, 401);
		}		
		
		$page = $this->get('page');
		if ($page == false) 
			$page = 1;
		$numperpage = $this->get('numperpage');
		if ($numperpage == false) 
			$numperpage = 10;

		$this->load->helper('validation_helper');
		if ((validate_int($page) == false && $page <= 0) || (validate_int($numperpage) == false && $numperpage <= 0)) {
			$resp['status'] = 400;
			$resp['message'] = 'Input invalid.';
			$this->response($resp, 400);
		}			
			
		$quotes = $this->quote_model->get_unvalidated_quotes($page, $numperpage);
		$resp['data'] = $quotes;
		$this->response($resp, 200);
	}
}