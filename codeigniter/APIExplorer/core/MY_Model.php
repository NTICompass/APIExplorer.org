<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

trait API_Module{
	function getDataSets($dataSet=NULL){}
	function getDataSetFields($dataSet=NULL){}
	function queryAPI($dataSet, $options=array(), $array=TRUE){}
	function queryAPI_id($dataSet, $_id, $array=TRUE){}
}

/**
 * The base for all loadable API modules
 *
 * This is so I can abstract out any API and have them all
 * communicate with the controller using the same methods
 */
class MY_Model extends CI_Model{

	function __construct(){
		parent::__construct();

		// Enable Caching
		$this->load->driver('cache', [
			'adapter' => 'apc',
			'backup' => 'file'
		]);
	}
}
