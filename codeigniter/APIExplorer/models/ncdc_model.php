<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API Module for NOAA's NCDC (now NCEI)
 * https://www.ncdc.noaa.gov/cdo-web/webservices/v2
 */
class NCDC_Model extends MY_Model{
	use API_Module;

	// URLs for the JSON metadata
	private $dataSetsURL = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/datasets';
	private $dataSetsFieldsURL = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/datasets/%s';
	// and the main API URL
	private $apiURL = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/%s';
	private $apiURL_id = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/%s/%s';

	// This API requires a token for access
	private $token;

	function __construct(){
		parent::__construct();

		$this->load->config('tokens');
		$this->token = $this->config->item('ncdc_noaa', 'tokens');
	}

	function getDataSets($dataSet=NULL){
		$ch = curl_init($this->dataSetsURL);
		curl_setopt_array($ch, [
			CURLOPT_HTTPHEADER => [
				'token: '.$this->token
			],
			CURLOPT_RETURNTRANSFER => TRUE
		]);
		$dataSets = json_decode(curl_exec($ch));
		curl_close($ch);

		var_dump($dataSets);
	}

	function getDataSetFields($dataSet=NULL){
	}

	function queryAPI($dataSet, $options=array(), $array=TRUE){
	}

	function queryAPI_id($dataSet, $_id, $array=TRUE){
	}
}
