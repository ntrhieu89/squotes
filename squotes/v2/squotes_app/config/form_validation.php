<?php 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$config = array(
	'admin_add_quote' => array(
		array(
			'field' => 'content',
			'label' => 'Content',
			'rules' => 'required|max_length[1000]|xss_clean',	
		),
		array(
			'field' => 'author',
			'label' => 'Author',
			'rules' => 'required|max_length[100]|xss_clean',
		),
		array(
			'field' => 'language',
			'label' => 'Language',
			'rules' => 'max_length[20]|xss_clean',
		),
	),
	'signup' => array(
			array(
					'field' => 'username',
					'label' => 'Username',
					'rules' => 'trim|required|max_length[30]|xss_clean|is_unique[users.username]|alpha_dash',
			),
			array(
					'field' => 'password',
					'label' => 'Password',
					'rules' => 'trim|required|max_length[30]|xss_clean|alpha_dash'
			),
// 			array(
// 					'field' => 'email',
// 					'label' => 'Email',
// 					'rules' => 'trim|required|valid_email|max_length[50]|xss_clean|is_unique[users.email]'
// 			),
	),
	'quote_like_fav_share' => array(
		array(
			'field' => 'quoteid',
			'label' => 'Quote ID',
			'rules' => 'required|integer|greater_than[0]'
		),
	),
	'quote_report' => array(
		array(
			'field' => 'quoteid',
			'label' => 'Quote ID',
			'rules' => 'required|integer|greater_than[0]'
		),
	),
	'quote_post' => array(
		array(
			'field' => 'content',
			'label' => 'Content',
			'rules' => 'required|min_length[10]|max_length[1000]|is_unique[quotes.content]|xss_clean'
		),
	),
);

/* End of file form_validation.php */
/* Location: ./application/config/form_validation.php */