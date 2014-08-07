<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @author Hieu Nguyen
 */

require APPPATH.'/controllers/api/BASE_Controller.php';

class Quotes extends BASE_Controller {
	function __construct() {
		parent::__construct();
		
		// loading the database
		$this->load->model('user_model');
		$this->load->model('quote_model');
		
		$this->load->library('form_validation');
		$this->load->helper('validation_helper');
	}
	
	/**
	 * GET /quotes
	 * Gets all quotes in the system
	 */
	function index_get() {
		$resp = $this->prepare_response();
		
		// check access_token if existed
		$userid = $this->check_authentication();
		
		$page = $this->get('page');
		if ($page == false) 
			$page = 1;		
		
		if (!validate_int($page) || $page <= 0) {
			$resp['status'] = 400;
			$resp['message'] = 'Page parameter is missing or the value is invalid.';
			$this->response($resp, 400);
		}
		
		$numperpage = $this->get('numperpage');
		if ($numperpage == false)
			$numperpage = 10;
		
		if (!validate_int($numperpage) || $numperpage <= 0) {
			$resp['status'] = 400;
			$resp['message'] = 'Number per page parameter is missing or the value is invalid.';
			$this->response($resp, 400);
		}
		
		$sorttype = $this->get('sorttype');		
		if ($sorttype == false)
			$sorttype = 'RECENT';
		
		$sorttype = strtoupper($sorttype);		
		if ($sorttype != 'RECENT' && $sorttype != 'HOT') {
			$resp['status'] = 400;
			$resp['message'] = 'Sort type is invalid.';
			$this->response($resp, 400);			
		}
			
		$quotetype = $this->get('quotetype');
		if ($quotetype == false)
			$quotetype = 'ALL';
		
		$quotetype = strtoupper($quotetype);
		if ($quotetype != 'ALL' && $quotetype != 'FAMOUS_QUOTE' && $quotetype != 'USER_QUOTE') {
			$resp['status'] = 400;
			$resp['message'] = 'Quote type is invalid.';
			$this->response($resp, 400);
		}
		
		$quotes = $this->quote_model->get_quotes($page, $numperpage, $sorttype, $quotetype);
		
		if ($quotes == false)
			$resp['data'] = null;
		else {
			foreach ($quotes as $key => $quote) {
				$quotes[$key] = $this->_build_quote_resp($quote, $userid);
			}
			
			$resp['data'] = $quotes;
		}
		
		$this->response($resp, 200);		
	}
	
	/**
	 * GET /quotes/aftertime
	 * Gets all quotes that have createdtime or publishedtime greater than than the provided timestamp
	 */
	function quotes_aftertime_get() {		
		$resp = $this->prepare_response();
		
		// check access_token if existed
		$userid = $this->check_authentication();		

		$time = $this->get('time');		
		if ($time == false) {
			$resp['status'] = 400;
			$resp['message'] = 'Input time is required.';
			$this->response($resp, 400);
		}
			
		$timetype = $this->get('timetype');
		if ($timetype == false)
			$timetype = 'CREATED_TIME';
		
		$this->load->helper('validation_helper');
		if (validate_date($timetype) == false) {
			$resp['status'] = 400;
			$resp['message'] = 'Input timetype is invalid.';
			$this->response($resp, 400);
		}

		$quotes = $this->quote_model->get_quotes_aftertime($timetype, $time);
		
		if ($quotes == false)
			$resp['data'] = null;
		else {
			foreach ($quotes as $key => $quote) {
				$quotes[$key] = $this->_build_quote_resp($quote, $userid);
			}
			
			$resp['data'] = $quotes;
		}
		
		$this->response($resp, 200);	
	}
	
	/**
	 * GET /quotes/beforetime
	 * Gets a number of quotes that have createdtime or publishedtime right before the provided timestamp
	 */
	function quotes_beforetime_get() {
		$resp = $this->prepare_response();
		
		// check access_token if existed
		$userid = $this->check_authentication();	

		$time = $this->get('time');
		if ($time == false) {
			$resp['status'] = 400;
			$resp['message'] = 'Input time is required.';
			$this->response($resp, 400);
		}

		$timetype = $this->get('timetype');
		if ($timetype == false)
			$timetype = 'CREATED_TIME';
		
		$this->load->helper('validation_helper');
		if (validate_date($timetype) == false) {
			$resp['status'] = 400;
			$resp['message'] = 'Input timetype is invalid.';
			$this->response($resp, 400);
		}

		$quotenum = $this->get('quotenum');
		if ($quotenum == false)
			$quotenum = 10;
		
		if (is_int($quotenum) == false || $quotenum <= 0) {
			$resp['status'] = 400;
			$resp['message'] = 'quotenum parameter is missing or the value is invalid.';
			$this->response($resp, 400);
		}		
		
		$this->quote_model->get_quotes_beforetime($timetype, $time, $quotenum);
		
		if ($quotes == false)
			$resp['data'] = null;
		else {
			foreach ($quotes as $key => $quote) {
				$quotes[$key] = $this->_build_quote_resp($quote, $userid);
			}		
				
			$resp['data'] = $quotes;
		}
		
		$this->response($resp, 200);
	}
	
	/**
	 * GET /user/quotes
	 * Gets all quotes posted by a user
	 */
	function quotes_user_get() {		
		$resp = $this->prepare_response();
		
		// check access_token if existed
		$id = $this->check_authentication();	
		
		$userid = $this->get('userid');
		
		if ($userid == false || !is_int((int)$userid)) {
			$resp['status'] = 400;
			$resp['message'] = 'User id missing.';
			$this->response($resp, 400);
		}
		
		$quotes = $this->quote_model->get_quotes_by_userid($userid);
		
		if ($quotes == false)
			$resp['data'] = null;
		else {
			foreach ($quotes as $key => $quote) {
				$quotes[$key] = $this->_build_quote_resp($quote, $id);
			}
		
			$resp['data'] = $quotes;
		}
		
		$this->response($resp, 200);		
	}
	
	/**
	 * GET user/favorites
	 * Gets all quotes this user favorites
	 */
	function quotes_user_favorites_get() {
		$resp = $this->prepare_response();
		
		// check access_token if existed
		$id = $this->check_authentication();	
		
		$userid = $this->get('userid');
		
		if ($userid == false || is_int($userid)) {
			$resp['status'] = 400;
			$resp['message'] = 'User id missing.';
			$this->response($resp, 400);
		}
		
		$quotes = $this->quote_model->get_fav_quotes($userid);
		
		if ($quotes == false)
			$resp['data'] = null;
		else {
			foreach ($quotes as $key => $quote) {
				$quotes[$key] = $this->_build_quote_resp($quote, $id);				
			}
		
			$resp['data'] = $quotes;
		}
		
		$this->response($resp, 200);	
	}
	
	/**
	 * GET /quote
	 * Gets most recent published quote
	 */
	function quote_get() {
		$resp = $this->prepare_response();
		
		// check access_token
		$userid = $this->check_authentication();
		
		$quote = $this->quote_model->get_recent_published_quote();
		
		if ($quote == false) {	
			$resp['status'] = 404;		
			$resp['message'] = 'No quote has been found.';
			$this->response($resp, 404);		
		} else {
			$quote = $this->_build_quote_resp($quote, $userid);
			
			$resp['data'] = $quote;
			$this->response($resp, 200);
		}
	}
	
	/**
	 * POST /quote
	 * User posts a quote
	 */
	function quote_post() {
		$resp = $this->prepare_response();
		
		// check access_token
		$userid = $this->check_authentication();		
		if ($userid == false) {
			$resp['status'] = 401;
			$resp['message'] = 'User unauthorized.';
			$this->response($resp, 401);
		} 
					
		if ($this->form_validation->run('quote_post') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 401);			
		}
		
		$content = $this->post('content');		
		
		$user = $this->user_model->get_user_by_id($userid);
		
		if ($user == false) {
			$resp['status'] = 500;
			$resp['message'] = 'Some error happens with the server. Try again later.';
			$this->response($resp, 500);				
		}
		
		$authorname = $user['firstname'];
		if ($user['lastname'] != '')
			$authorname = $authorname.' '.$user['lastname'];
			
		if ($this->quote_model->add_quote($userid, $authorname, $content, false) == true) {
			$id = mysql_insert_id();
			$quote = $this->quote_model->get_quote($id);
				
			if ($quote == false) {
				$resp['status'] = 404;
				$resp['message'] = 'Add quote success but then cannot retrieve the data';
				$this->response($resp, 404);
			}
				
			$quote = $this->_build_quote_resp($quote, $userid);
			$this->response($quote, 200);
		} else {
			$resp = array('error', 'Cannot add quote. Maybe it is posted before. Try again later.');
			$this->response($resp, 500);
		}		
	}
	
	/**
	 * POST quote/like
	 * User likes a quote
	 */
	function quote_like_post() {
		$resp = $this->prepare_response();	
		
		$userid = $this->check_authentication();		
		if ($userid == false) {	// userid missing
			$resp['status'] = 400;
			$resp['message'] = 'User unauthorized.';
			$this->response($resp, 400);
		}
		
		if ($this->form_validation->run('quote_like_fav_share') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 400);
		}
		
		$quoteid = $this->post('quoteid');
		if ($this->quote_model->is_existed($quoteid) == false) {
			$resp['status'] = 404;
			$resp['message'] = 'Quote with quoteid provided not found.';
			$this->response($resp, 404);
		}
		
		if ($this->quote_model->take_action_quote($userid, $quoteid, 'like') == true) {
			$this->response($resp, 200);
		} else {
			$resp['status'] = 500;
			$resp['message'] = 'Quote not found or server error. Try again';
			$this->response($resp, 500);
		}
	}
	
	function quote_unlike_post() {
		
	}
	
	function quote_unfavorite_port() {
		
	}
	
	/**
	 * POST quote/favorite
	 * User favors a quote
	 */
	function quote_favorite_post() {
		$resp = $this->prepare_response();	
		
		$userid = $this->check_authentication();		
		if ($userid == false) {	// userid missing
			$resp['status'] = 400;
			$resp['message'] = 'Userid missing.';
			$this->response($resp, 400);
		}
		
		if ($this->form_validation->run('quote_like_fav_share') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 400);
		}
		
		$quoteid = $this->post('quoteid');
		if ($this->quote_model->is_existed($quoteid) == false) {
			$resp['status'] = 404;
			$resp['message'] = 'Quote with quoteid provided not found.';
			$this->response($resp, 404);
		}
		
		if ($this->quote_model->take_action_quote($userid, $quoteid, 'favorite') == true) {
			$this->response($resp, 200);
		} else {
			$resp['status'] = 500;
			$resp['message'] = 'Server error. Try again';
			$this->response($resp, 500);
		}
	}
	
	/**
	 * POST quote/share
	 */
	function quote_share_post() {
		$resp = $this->prepare_response();
	
		$userid = $this->check_authentication();
			if ($userid == false || !is_int((int)$userid)) {
			$resp['status'] = 400;
			$resp['message'] = 'Userid missing.';
			$this->response($resp, 400);			
		}	
	
		if ($this->form_validation->run('quote_like_fav_share') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 400);
		}
	
		$quoteid = $this->post('quoteid');
		if ($this->quote_model->is_existed($quoteid) == false) {
			$resp['status'] = 404;
			$resp['message'] = 'Quote with quoteid provided not found.';
			$this->response($resp, 404);
		}
		
		if ($this->quote_model->take_action_quote($userid, $quoteid, 'share') == true) {
			$this->response($resp, 200);
		} else {
			$resp['status'] = 500;
			$resp['message'] = 'Server error. Try again';
			$this->response($resp, 500);
		}
	}
	
	/**
	 * POST quote/report
	 * User reports a quote as inappropriate
	 */
	function quote_report_post() {
		$resp = $this->prepare_response();
		
		$userid = $this->check_authentication();
		
		if ($userid == false || !is_int((int)$userid)) {
			$resp['status'] = 400;
			$resp['message'] = 'Userid missing.';
			$this->response($resp, 400);			
		}		
		
		if ($this->form_validation->run('quote_report') == false) {
			$resp['status'] = 400;
			$resp['message'] = $this->get_validation_errors($this->form_validation->error_array());
			$this->response($resp, 400);
		}
		
		$quoteid = $this->post('quoteid');
		if ($this->quote_model->is_existed($quoteid) == false) {
			$resp['status'] = 404;
			$resp['message'] = 'Quote with quoteid provided not found.';
			$this->response($resp, 404);
		}
		
		$message = $this->post('message');
		if ($message == false)
			$message = '';
		
		if ($this->quote_model->report_quote($userid, $quoteid, $message) == true) {
			$this->response($resp, 200);
		} else {
			$resp['status'] = 500;
			$resp['message'] = 'Server error. Try again';
			$this->response($resp, 500);
		}		
	}
	
	/**
	 * Buils quote response by adding the quote data with some user context related to that quote
	 * @param unknown $quote
	 * @param unknown $userid
	 * @return boolean|NULL
	 */
	private function _build_quote_resp($quote, $userid) {
		if ($quote == false)
			return false;
		
		// convert time
		$this->load->helper('converter_helper');
		if ($quote['createdtime'] != null)
			$quote['createdtime'] = time_to_unixtimestamp($quote['createdtime'], 'America/Los_Angeles');
		if ($quote['publishedtime'] != null)
			$quote['publishedtime'] = time_to_unixtimestamp($quote['publishedtime'], 'America/Los_Angeles');
		
		if (validate_int($userid)) {
			$quote['is_liked'] = $this->quote_model->is_liked($quote['quoteid'], $userid);
			$quote['is_favored'] = $this->quote_model->is_favored($quote['quoteid'], $userid);
			$quote['is_reported'] = $this->quote_model->is_reported($quote['quoteid'], $userid);		
		} else {
			$quote['is_liked'] = null;
			$quote['is_favored'] = null;
			$quote['is_reported'] = null;			
		}
			
		return $quote;
	}
}