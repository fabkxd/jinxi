<?php
class Index extends CI_Controller {
	public function __construct() {
		parent::__construct ();
		$this->load->model ( 'user_model' );
		$this->load->library('session');
		$this->load->helper('url');
	}
	public function index() {
		$this->output->enable_profiler(FALSE);
		$user_id =  $this->session->userdata('login_user');
		
		if($user_id != ''){
			$login_user = $this->user_model->get_user($user_id);
			$data ['login_user'] = $login_user;
		}
		$data ['choose'] = 1;
		$data ['baseurl'] = base_url();
		$data ['title'] = '主页';

		
		$this->load->view ( 'templates/header', $data );
		$this->load->view ( 'index/main' );
		$this->load->view ( 'index/right' );
		$this->load->view ( 'templates/footer' );

	}
}