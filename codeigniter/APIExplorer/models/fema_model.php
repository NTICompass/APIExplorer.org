<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API Module for OpenFEMA
 * http://www.fema.gov/openfema-api-documentation
 */
class FEMA_Model extends MY_Model{
	use API_Module;

	// URLs for the JSON metadata
	private $dataSetsURL = 'http://www.fema.gov/api/open/v1/OpenFemaDataSets.json';
	private $dataSetsFieldsURL = 'http://www.fema.gov/api/open/v1/OpenFemaDataSetFields.json';
	// and the main API URL
	private $apiURL = 'http://www.fema.gov/api/open/v1/%s?$format=json';
	private $apiURL_id = 'http://www.fema.gov/api/open/v1/%s/%s?$format=json';

	private $dataSets;
	private $dataSetsFields;

	public static $filterFuncs = [
		'eq' => "%s eq '%s'",
		'ne' => "%s ne '%s'",
		'gt' => "%s gt '%s'",
		'ge' => "%s ge '%s'",
		'lt' => "%s lt '%s'",
		'le' => "%s le '%s'",
		# substringof is (searchString, field)
		'!substringof' => "substringof('%s', %s)",
		'endswith' => "endswith(%s, '%s')",
		'startswith' => "startswith(%s, '%s')",
		'!not_substringof' => "not substringof('%s', %s)",
		'not_endswith' => "not endswith(%s, '%s')",
		'not_startswith' => "not startswith(%s, '%s')"
	];

	function __construct(){
		parent::__construct();

		// Check the cache, this saves us some API/JSON calls
		$this->dataSets = $this->cache->get('dataSets') ?: NULL;
		$this->dataSetsFields = $this->cache->get('dataSetsFields') ?: NULL;
	}

	function getDataSets($dataSet=NULL){
		if(is_null($this->dataSets)){
			$this->dataSets = [];

			// FEMA doesn't encode this properly, so I need to fix apostrophes
			// http://www.fileformat.info/info/unicode/char/fffd/index.htm
			$dataSetsJSON = preg_replace('/\xEF\xBF\xBD/', '\'', file_get_contents($this->dataSetsURL));
			// Fix FEMA's JSON formatting
			$dataSets = json_decode('['.str_replace('}{', '},{', $dataSetsJSON).']');

			foreach($dataSets as $set){
				$this->dataSets[$set->name] = $set;
			}

			$this->cache->save('dataSets', $this->dataSets, 600);
		}

		return $dataSet ? $this->dataSets[$dataSet] : $this->dataSets;
	}

	function getDataSetFields($dataSet=NULL){
		if(is_null($this->dataSetsFields)){
			$this->dataSetsFields = [];

			// FEMA doesn't encode this properly, so I need to fix apostrophes
			// http://www.fileformat.info/info/unicode/char/fffd/index.htm
			$dataSetsFieldsJSON = preg_replace('/\xEF\xBF\xBD/', '\'', file_get_contents($this->dataSetsFieldsURL));

                        // Fix FEMA's JSON formatting
                        $dataSetsFields = json_decode('['.str_replace('}{', '},{', $dataSetsFieldsJSON).']');

			foreach($dataSetsFields as $fields){
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
