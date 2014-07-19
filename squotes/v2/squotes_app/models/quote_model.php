<?php

/**
 * Quote Model
 * @author Hieu
 *
 */
class Quote_model extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	/**
	 * Adds a famous quote to the system.
	 * @param unknown $content
	 * @param unknown $author
	 * @param unknown $contributor
	 */
	public function add_famous_quote($content, $author, $language) {		
		if ($author == false)
			$author = 'Unknown';
		
		if ($language == false)
			$language = 'english';
		
		$this->db->set('publishedtime', 'NOW()', false);
		$quote_data = array(
				'content' => $content,
				'userid' => null,
				'ispublished' => true,			
				'authorname' => $author,
				'type' => 0,
				'language' => $language
		);
		
		if (!$this->db->insert('quotes', $quote_data))
			return false;
		
		return true;
	}
	
	/**
	 * Adds a suggested quote to the system.
	 * @param unknown $userid
	 * @param unknown $authorname
	 * @param unknown $content
	 * @param unknown $language
	 * @return boolean
	 */
	public function add_quote($userid, $authorname, $content, $language) {
		if ($language == false)
			$language = 'english';
		
		$this->db->set('publishedtime', 'NOW()', false);
		$quote_data = array(
			'content' => $content,
			'userid' => $userid,
			'ispublished' => true,
			'authorname' => $authorname,
			'type' => 1,
			'language' => $language
		);
		
		if (!$this->db->insert('quotes', $quote_data))
			return false;
		
		return true;		
	}
	
	/**
	 * Gets quotes
	 * @param unknown $page	Page index
	 * @param unknown $numperpage	Number of quotes per page
	 * @param unknown $timetype	Time sorted based (createdtime or publishedtime)
	 * @param unknown $quotetype Type of quotes want to retrieve (famous quote or user quote)
	 * @return boolean|multitype:
	 */
	public function get_quotes($page, $numperpage, $sorttype, $quotetype) {
		$query = null;

		switch ($sorttype) {
		case 'RECENT':
			switch ($quotetype) {
			case 'ALL':
				$query = $this->db->query('select * from quotes order by createdtime desc limit '.($page - 1).', '.$numperpage);
				break;
			case 'FAMOUS_QUOTE':
				$query = $this->db->query('select * from quotes where userid is null order by createdtime desc limit '.($page - 1).', '.$numperpage);
				break;
			case 'USER_QUOTE':
				$query = $this->db->query('select * from quotes where userid is not null order by createdtime desc limit '.($page - 1).', '.$numperpage);
				break;
			}
			break;
		case 'HOT':
			switch ($quotetype) {
			case 'ALL':
				$query = $this->db->query('select *, (likecount+sharecount+favcount) as credit_count from quotes where ispublished=1 order by credit_count desc limit '.($page - 1).', '.$numperpage);
				break;
			case 'FAMOUS_QUOTE':
				$query = $this->db->query('select *, (likecount+sharecount+favcount) as credit_count from quotes where ispublished=1 and userid is null order by credit_count desc limit '.($page - 1).', '.$numperpage);
				break;
			case 'USER_QUOTE':
				$query = $this->db->query('select *, (likecount+sharecount+favcount) as credit_count from quotes where ispublished=1 and userid is not null order by credit_count desc limit '.($page - 1).', '.$numperpage);
				break;
			}
		}
		
		if ($query == null || $query->num_rows() == 0)
			return false;
		else {
			$quotes = ($query->result_array());
			return $quotes;
		}		
	}
	
	/**
	 * Gets all new quotes that has created time or published time after a specific time provided
	 * @param unknown $timetype
	 * @param unknown $time
	 * @return boolean|multitype:
	 */
	public function get_quotes_aftertime($timetype, $time) {				
		$query = null;
		switch ($timetype) {
			case 'CREATED_TIME':
				$query = $this->db->query('select * from quotes where $createdtime > '.$time.' order by createdtime desc');
				break;
			case 'PUBLISHED_TIME':
				$query = $this->db->query('select * from quotes where $publishedtime > '.$time.' order by publishedtime desc');
				break;
		}
		
		if ($query == null || $query->num_rows() == 0)
			return false;
		else {
			$quotes = $query->result_array();			
			return $quotes;			
		}			
	}
	
	/**
	 * Gets a number of quotes that have createdtime or publishedtime before a given time.
	 * @param unknown $timetype
	 * @param unknown $time
	 * @param unknown $quotenum
	 * @return boolean|multitype:
	 */
	public function get_quotes_beforetime($timetype, $time, $quotenum) {
		$query = null;
		switch ($timetype) {
			case 'CREATEDTIME':
				$query = $this->db->query('select * from quotes where $createdtime < '.$time.' order by createdtime desc limit '.$quotenum);
				break;
			case 'PUBLISHEDTIME':
				$query = $this->db->query('select * from quotes where $publishedtime < '.$time.' order by publishedtime desc limit '.$quotenum);
				break;
		}
		
		if ($query == false || $query->num_rows() == 0)
			return false;
		else {
			$quotes = $query->result_array();
			return $quotes;
		}		
	}
	
	/**
	 * Gets all quotes posted by a user provided by userid
	 * @param unknown $userid
	 * @return boolean|multitype:
	 */
	public function get_quotes_by_userid($userid) {
		$query = $this->db->query('select * from quotes where userid='.$userid.' order by createdtime desc');
		
		if ($query == null || $query->num_rows() == 0)
			return false;
		
		$quotes = $query->result_array();
		return $quotes;
	}
	
	/**
	 * Gets all quotes favorited by a user
	 * @param unknown $userid
	 * @return boolean|multitype:
	 */
	public function get_fav_quotes($userid) {
		$query = $this->db->query('select quotes.* from quotes, favorites where quotes.quoteid= favorites.quoteid and favorites.userid='.$userid.' order by createdtime desc');
		
		if ($query == null || $query->num_rows() == 0)
			return false;
		
		$quotes = $query->result_array();
		return $quotes;		
	}
	
	/**
	 * 
	 * @param unknown $userid
	 * @param unknown $quoteid
	 * @return boolean
	 */
	public function take_action_quote($userid, $quoteid, $action) {
		$data = array(
			'userid' => $userid,
			'quoteid' => $quoteid,
		);
		
		if ($action != 'like' && $action != 'favorite' && $action != 'share')
			return false;
		
		$this->db->trans_begin();
		$success = true; 
		switch ($action) {
		case 'like':
			$this->db->query('insert ignore into likes(userid, quoteid) values('.$userid.', '.$quoteid.')');
			if ($this->db->affected_rows() == 1) {
				$this->db->query('update quotes set likecount=likecount+1 where quoteid='.$quoteid);
				if ($this->db->affected_rows() != 1) {
					$succes = false;
				}
			} else 
				$success = false;
			break;
		case 'favorite':
			$this->db->query('insert ignore into favorites(userid, quoteid) values('.$userid.', '.$quoteid.')');
			if ($this->db->affected_rows() == 1) {
				$this->db->query('update quotes set favcount=favcount+1 where quoteid='.$quoteid);
				if ($this->db->affected_rows() != 1) {
					$succes = false;
				}
			} else 
				$success = false;
			break;
		case 'share':
			$this->db->query('update quotes set sharecount=sharecount+1 where quoteid='.$quoteid);
			if ($this->db->affected_rows() != 1) {
				$succes = false;
			}
			break;
		default:
			$sucess = false;
			break;
		}
		
		if ($sucess = true) {
			$this->db->trans_commit();
			return true;
		} else {
			$this->db->trans_rollback();
			return false;			
		}
	}
	
	/**
	 * Reports a quote as inappropriate
	 * @param unknown $userid
	 * @param unknown $quoteid
	 * @param unknown $message
	 */
	public function report_quote($userid, $quoteid, $message) {
		$data = array(
				'userid' => $userid,
				'quoteid' => $quoteid,
				'message' => $message
		);

		$this->db->trans_begin();
		$success = true;	

		$this->db->insert('quotereports', $data);
		if ($this->db->affected_rows() == 1) {
			$this->db->query('update quotes set reportcount=reportcount+1 where quoteid='.$quoteid);
			if ($this->db->affected_rows() != 1) {
				$succes = false;
			}
		} else
			$success = false;	

		if ($sucess = true) {
			$this->db->trans_commit();
			return true;
		} else {
			$this->db->trans_rollback();
			return false;
		}		
	}

	/**
	 * Gets quote by id
	 * @param unknown $quoteid
	 * @return boolean|multitype:
	 */
	public function get_quote($quoteid) {
		$query = $this->db->query('select * from quotes where quoteid='.$quoteid);
		
		if ($query == null || $query->num_rows() != 1)
			return false;
		
		$quote = get_object_vars($query->result()[0]);
		return $quote;
	}
	
	/**
	 * Check whether user with userid provided likes quote with quoteid provided
	 * @param unknown $quoteid
	 * @param unknown $userid
	 */
	public function is_liked($quoteid, $userid) {
		$query = $this->db->query('select * from likes where userid='.$userid.' and quoteid='.$quoteid);
		
		if ($query == false || $query->num_rows() != 1)
			return false;
		else
			return true;
	}
	
	/**
	 * Check whether user with userid provided favors quote with quoteid provided
	 * @param unknown $quoteid
	 * @param unknown $userid
	 */
	public function is_favored($quoteid, $userid) {
		$query = $this->db->query('select * from favorites where userid='.$userid.' and quoteid='.$quoteid);
		
		if ($query == false || $query->num_rows() != 1)
			return false;
		else
			return true;		
	}
	
	/**
	 * Check whether user with userid provided reports quote with quoteid provided as inappropriate
	 * @param unknown $quoteid
	 * @param unknown $userid
	 */
	public function is_reported($quoteid, $userid) {
		$query = $this->db->query('select * from quotereports where userid='.$userid.' and quoteid='.$quoteid);
	
		if ($query == false || $query->num_rows() != 1)
			return false;
		else
			return true;
	}
	
	/**
	 * Validates a quote. Then it is ready to be published
	 * @param unknown $quoteid
	 * @return void|boolean
	 */
	public function publish_quote($quoteid) {
		if ($quoteid == false)
			return;
		
		$data = array('ispublished', true);
		
		$this->db->set('publishedtime', 'NOW()', false);
		$this->db->where('quoteid', $quoteid);
		$this->db->update('quotes', $data);
		
		if ($this->db->affected_rows() == 1)
			return true;
		else
			return false;
	}
	
// 	/**
// 	 * Gets a quote to publish. 
// 	 * A satisfied quote is the one which is validated and has not been published yet.
// 	 * If no quote satisfies the condition, null value is returned.
// 	 */
// 	public function get_a_quote_to_publish($language) {
// 		// gets the oldest quote that has been validated but has not been published yet
// 		$query = $this->db->query('select * from quotes where isvalidated=1 and ispublished=0 and language=\''.$language.'\' order by createdtime desc limit 1');
		
// 		// no quote found. may be there is no validated quote is the database or all validated quotes were published.
// 		if ($query == null || $query->num_rows() != 1)
// 			return false;
		
// 		$quote = get_object_vars($query->result()[0]);
		
// 		return $quote;
// 	}
	
// 	/**
// 	 * The quote (if existed) is marked as published.
// 	 * @param unknown $quoteid
// 	 * @return boolean
// 	 */
// 	public function set_a_quote_as_published($quoteid) {
// 		if ($quoteid == false)
// 			return false;
		
// 		$data = array('ispublished', true);
		
// 		$query = $this->db->query('update quotes set ispublished=1, publishedtime=now() where quoteid='.$quoteid);
		
// 		if ($this->db->affected_rows() == 1)
// 			return true;
// 		else
// 			return false;
// 	}
	
	public function get_recent_published_quote() {
		$query = $this->db->query('select * from quotes where ispublished=1 order by publishedtime desc limit 1');
		
		if ($query == true && $query->num_rows() == 1) {
			$quote = get_object_vars($query->result()[0]);
			return $quote;
		} else 
			return false;
	}
	
	
	/**
	 * Gets all uninvalited quotes from the database
	 * @return NULL|multitype:
	 */
	public function get_unvalidated_quotes() {
		$query = $this->db->query('select * from quotes where isvalidated=0');
		
		if ($query == null || $query->num_rows() == 0)
			return null;
		
		$quotes = get_object_vars($query->result());
		
		return $quotes;		
	}
	
	/**
	 * Deletes a quote.
	 * This should be used when a quote is not qualified and cannot be validated.
	 */
	public function delete_quote($quoteid) {
		if ($quoteid == false)
			return false;
		
		$this->db->query('delete from quotes where quoteid='.$quoteid);
		
		if ($this->db->affected_rows() == 1)
			return true;
		else
			return false;
	}
	
	/**
	 * Checks whether a quote with quoteid exists
	 * @param unknown $quoteid
	 */
	public function is_existed($quoteid) {
		$query = $this->db->query('select * from quotes where quoteid='.$quoteid);
		
		if ($query == true && $query->num_rows() == 1)
			return true;
		else 
			return false;
	}
}