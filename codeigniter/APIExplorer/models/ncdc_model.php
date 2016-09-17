<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API Module for NOAA's NCDC (now NCEI)
 * https://www.ncdc.noaa.gov/cdo-web/webservices/v2
 * https://www.ncdc.noaa.gov/cdo-web/datasets
 * https://www.ncdc.noaa.gov/cdo-web/webservices
 */
class NCDC_Model extends MY_Model{
	use API_Module;

	// URLs for the JSON metadata
	private $dataSetsURL = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/datasets';
	// https://geo-ide.noaa.gov/wiki/index.php?title=ISO_FAQ#What_are_gmd:.2C_gmi:.2C_gml:.2C_....3F
	private $dataSetInfo = 'https://gis.ncdc.noaa.gov/geoportal/rest/document?id=%s';
	private $dataSetsFieldsURL = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/datatypes?datasetid=%s';
	// and the main API URL
	private $apiURL = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/data?datasetid=%s';
	private $apiURL_id = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/%s/%s';

	// This API requires a token for access
	private $token;

	// Dataset info
	private $dataSets;
	private $dataSetsFields;

	// XPath to get the data set description
	private $descriptionXPath = [
		'gmd:distributionInfo',
		'gmd:transferOptions',
		'gmd:onLine',
		'gmd:applicationProfile/gco:CharacterString[text()="Description"]/parent::*/parent::*',
		'gmd:description/gco:CharacterString'
	];

	function __construct(){
		parent::__construct();

		$this->load->config('tokens');
		$this->token = $this->config->item('ncdc_noaa', 'tokens');

		// Check the cache, this saves us some API/JSON calls
		$this->dataSets = $this->cache->get('noaa_dataSets') ?: NULL;
		$this->dataSetsFields = $this->cache->get('noaa_dataSetsFields') ?: NULL;
	}

	function getDataSets($dataSet=NULL){
		if(is_null($this->dataSets)){
			$this->dataSets = [];

			$ch = curl_init($this->dataSetsURL);
			curl_setopt_array($ch, [
				CURLOPT_HTTPHEADER => [
					'token: '.$this->token
				],
				CURLOPT_RETURNTRANSFER => TRUE
			]);
			$dataSets = json_decode(curl_exec($ch));
			curl_close($ch);

			foreach($dataSets->results as $result){
				// TODO: Do I *really* need to get an XML for every dataset?
				$infoXML = new SimpleXMLElement(sprintf($this->dataSetInfo, urlencode($result->uid)), NULL, TRUE);
				foreach($infoXML->getDocNamespaces() as $ns=>$ns_uri){
					// TODO: We only need gmi, gmd, and gco
					$infoXML->registerXPathNamespace($ns, $ns_uri);
				}

				// TODO: Is there a better way to parse this XML?
				$description = $infoXML->xpath('//'.implode('//', $this->descriptionXPath));

				$this->dataSets[$result->id] = (object)[
					'name' => $result->id,
					'title' => $result->name,
					'description' => count($description) ? (string)($description[0]) : ''
				];
			}

			$this->cache->save('noaa_dataSets', $this->dataSets, 600);
		}

		return $dataSet ? $this->dataSets[$dataSet] : $this->dataSets;
	}

	function getDataSetFields($dataSet=NULL){
		// SELECT https://www.ncdc.noaa.gov/cdo-web/webservices/v2#dataTypes
		// WHERE https://www.ncdc.noaa.gov/cdo-web/webservices/v2#data
		$this->dataSetsFields = [];
		$this->dataSetsFields[$dataSet] = [];

		// Even this has a "limit" parameter.  We want all of the fields.
		$ch = curl_init(sprintf($this->dataSetsFieldsURL, urlencode($dataSet)).'&limit=1000');
		curl_setopt_array($ch, [
			CURLOPT_HTTPHEADER => [
				'token: '.$this->token
			],
			CURLOPT_RETURNTRANSFER => TRUE
		]);
		$dataSetFields = json_decode(curl_exec($ch));
		curl_close($ch);

		foreach($dataSetFields->results as $result){
			$this->dataSetsFields[$dataSet][] = (object)[
				'title' => $result->id,
				'name' => $result->id,
				'description' => $result->name,
				'type' => 'string'
			];
		}

		// $this->cache->save('noaa_dataSetsFields', $this->dataSetsFields, 600);

		return $this->dataSetsFields[$dataSet];
	}

	function queryAPI($dataSet, $options=array(), $array=TRUE){
		// TODO: This API doesn't filter on its return fields.
		// Instead, it filters based on location, station, and dates
		// TODO: Manually enter in WHERE info (to use instead of fields)
		// TODO: Sort: Supports id, name, mindate, maxdate, and datacoverage fields.
		$query = [
			# datatypeid is used to specify which fields to include
			# Docs say "ampersands", commas seem to work
			'datatypeid' => is_array($options['select']) ? implode(',', $options['select']) : '',
			# Required Params
			'startdate' => '2010-05-01',
			'enddate' => '2010-05-01',
			# Optional Params
			'locationid' => 'ZIP:28801',
			'limit' => isset($options['top']) ? $options['top'] : '',
			'offset' => isset($options['skip']) ? $options['skip'] : '',
			'includemetadata' => isset($options['inlinecount']) ? 'true' : 'false'
		];

		$ch = curl_init(sprintf($this->apiURL, urlencode($dataSet)).'&'.http_build_query($query));
		curl_setopt_array($ch, [
			CURLOPT_HTTPHEADER => [
				'token: '.$this->token
			],
			CURLOPT_RETURNTRANSFER => TRUE
		]);
		$apiQuery = curl_exec($ch);
		curl_close($ch);

		die($apiQuery);
	}

	function queryAPI_id($dataSet, $_id, $array=TRUE){
	}
}
