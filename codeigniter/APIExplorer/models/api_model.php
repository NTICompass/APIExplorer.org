<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class API_Model extends CI_Model{

	function __construct(){
		parent::__construct();
	}

	function get_available_apis(){
		$this->db->select('api_id,name,description')
			->from('available_apis')
			->where('siteType', 'live')
			->order_by('name');
		$query = $this->db->get();

		return $query->result();
	}

	function get_api_info($api_id){
		$this->db->select('name,logo,model_file')
			->from('available_apis')
			->where('api_id', $api_id);
		$query = $this->db->get();

		return $query->num_rows() === 1 ? $query->row() : NULL;
	}
}
