<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('API_Model');
	}

	public function index(){
		$this->load->view('main', [
			'apis' => $this->API_Model->get_available_apis()
		]);
	}
}
