<?php 
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

$config = array(
	'admin_add_quote' => array(
		array(
			'field' => 'content',
			'label' => 'Content',
			'rules' => 'required|is_unique[quotes.content]|max_length[1000]|xss_clean',	
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
				'field' => 'email',
				'label' => 'Email',
				'rules' => 'trim|required|valid_email|max_length[100]|xss_clean|is_unique[users.email]'
		),
		array(
				'field' => 'password',
				'label' => 'Password',
				'rules' => 'trim|required|min_length[6]|max_length[30]|xss_clean|alpha_dash'
		),
		array(
				'field' => 'firstname',
				'label' => 'First Name',
				'rules' => 'required|max_length[50]|xss_clean'
		),
		array(
				'field' => 'lastname',
				'label' => 'Last Name',
				'rules' => 'max_length[50]|alpha_numeric|xss_clean'
		),
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
	'set_avatar' => array(
			array(
					'field' => 'avatar',
					'label' => 'Avatar',
					'rules' => 'required|xss_clean|'
			)
	),
	'set_name' => array(
		array(
				'field' => 'firstname',
				'label' => 'First Name',
				'rules' => 'required|max_length[50]|xss_clean'
		),
		array(
				'field' => 'lastname',
				'label' => 'Last Name',
				'rules' => 'max_length[50]|alpha_numeric|xss_clean'
		),
	),
	'change_password' => array(
			array(
					'field' => 'oldpass',
					'label' => 'Old Password',
					'rules' => 'trim|required|max_length[128]|xss_clean|alpha_dash'
			),
			array(
					'field' => 'newpass',
					'label' => 'New Password',
					'rules' => 'trim|required|max_length[128]|xss_clean|alpha_dash'
			)
	),		
);

/* End of file form_validation.php */
/* Location: ./application/config/form_validation.php */