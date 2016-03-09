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
	private $dataSetsFieldsURL = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/datasets/%s';
	// and the main API URL
	private $apiURL = 'http://www.ncdc.noaa.gov/cdo-web/api/v2/%s';
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
				$description = $infoXML->xpath('//'.implode('//', $this->descriptionXPath))[0];

				$this->dataSets[] = (object)[
					'name' => $result->id,
					'title' => $result->name,
					'description' => (string)$description
				];
			}
		}

		return $this->dataSets;
	}

	function getDataSetFields($dataSet=NULL){
		// SELECT https://www.ncdc.noaa.gov/cdo-web/webservices/v2#dataTypes
		// WHERE https://www.ncdc.noaa.gov/cdo-web/webservices/v2#data
	}

	function queryAPI($dataSet, $options=array(), $array=TRUE){
	}

	function queryAPI_id($dataSet, $_id, $array=TRUE){
	}
}
