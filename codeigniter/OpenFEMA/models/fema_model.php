<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// http://www.fema.gov/openfema-api-documentation
class FEMA_Model extends CI_Model{
	// URLs for the JSON metadata
	private $dataSetsURL = 'http://www.fema.gov/api/open/v1/OpenFemaDataSets.json';
	private $dataSetsFieldsURL = 'http://www.fema.gov/api/open/v1/OpenFemaDataSetFields.json';
	// and the main API URL
	private $apiURL = 'http://www.fema.gov/api/open/v1/%s?$format=json';
	private $apiURL_id = 'http://www.fema.gov/api/open/v1/%s/%s?$format=json';

	private $dataSets;
	private $dataSetsFields;

	function __construct(){
		$this->load->driver('cache', [
			'adapter' => 'apc',
			'backup' => 'file'
		]);

		// Check the cache, this saves us some API/JSON calls
		$this->dataSets = $this->cache->get('dataSets') ?: NULL;
		$this->dataSetsFields = $this->cache->get('dataSetsFields') ?: NULL;
	}

	function getDataSets($dataSet=NULL){
		if(is_null($this->dataSets)){
			$this->dataSets = [];

			foreach(file($this->dataSetsURL) as $x){
				$set = json_decode($x);
				$this->dataSets[$set->name] = $set;
			}

			$this->cache->save('dataSets', $this->dataSets, 600);
		}

		return $dataSet ? $this->dataSets[$dataSet] : $this->dataSets;
	}

	function getDataSetFields($dataSet=NULL){
		if(is_null($this->dataSetsFields)){
			$this->dataSetsFields = [];

			foreach(file($this->dataSetsFieldsURL) as $x){
				$fields = json_decode($x);

				if(!isset($this->dataSetsFields[$fields->openFemaDataSet])){
					$this->dataSetsFields[$fields->openFemaDataSet] = [];
				}

				$this->dataSetsFields[$fields->openFemaDataSet][] = $fields;
			}

			$this->cache->save('dataSetsFields', $this->dataSetsFields, 600);
		}

		return $dataSet ? $this->dataSetsFields[$dataSet] : $this->dataSetsFields;
	}

	function queryAPI($dataSet, $options=array(), $array=TRUE){
		$query = [];
		foreach($options as $key=>$value){
			if(!is_null($value)){
				$query['$'.$key] = is_array($value) ? implode(',', $value) : $value;
			}
		}

		$url = sprintf($this->apiURL, $dataSet).(
			count($query) > 0 ? ('&'.http_build_query($query)) : ''
		);
		$data = file_get_contents($url);

		return $array ? json_decode($data) : $data;
	}

	function queryAPI_id($dataSet, $_id, $array=TRUE){
		$data = file_get_contents(sprintf($this->apiURL_id, $dataSet, $_id));

		return $array ? json_decode($data) : $data;
	}
}
