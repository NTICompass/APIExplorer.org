<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API Module for Google's Civic Information API
 * https://developers.google.com/civic-information/docs/v2/
 */
class Gcivic_Model extends MY_Model{
	use API_Module;

	public static $filterArray = TRUE;
	public static $filterFuncs = [
		'eq' => '',
		'ne' => '',
		'gt' => '',
		'ge' => '',
		'lt' => '',
		'le' => '',
		'substringof' => '',
		'endswith' => '',
		'startswith' => '',
		'not_substringof' => '',
		'not_endswith' => '',
		'not_startswith' => ''
	];

	private $queryURL = 'https://www.googleapis.com/civicinfo/v2';
	private $endpoints = [
		'Elections' => [
			'elections' => [
				'urls' => [
					'name' => 'elections',
					'title' => 'electionQuery',
					'description' => 'List of available elections to query.'
				],
				'fields' => [
				]
			],
			'voterinfo' => [
				'urls' => [
					'name' => 'voterinfo',
					'title' => 'voterInfoQuery',
					'description' => 'Looks up information relevant to a voter based on the voter\'s registered address.'
				],
				'fields' => [
				]
			]
		],
		'Representatives' => [
			'representatives' => [
				'urls' => [
					'name' => 'representatives',
					'title' => 'representativeInfoByAddress',
					'description' => 'Looks up political geography and representative information for a single address.'
				],
				'fields' => [
				]
			],
			'representatives_ocdId' => [
				'urls' => [
					'name' => 'representatives_ocdId',
					'title' => 'representativeInfoByDivision',
					'description' => 'Looks up representative information for a single geographic division.'
				],
				'fields' => [
				]
			]
		],
		'Divisions' => [
			'divisions' => [
				'urls' => [
					'name' => 'divisions',
					'title' => 'search',
					'description' => 'Searches for political divisions by their natural name or OCD ID.'
				],
				'fields' => [
					[
						'name' => 'query',
						'title' => 'Query',
						'description' => 'The search query'
					]
				]
			]
		]
	];

	function __construct(){
		parent::__construct();

		// I can't set a function as a value in the static array,
		// but I *can* change it here...
		if(self::$filterFuncs['eq'] === ''){
			self::$filterFuncs = array_merge(self::$filterFuncs, [
				'eq' => function($field, $data){
					return [$field => $data];
				}
			]);
		}
	}

	/**
	 * I'm not sure if there's an API call to get this.
	 */
	function getDataSets($dataSet=NULL){
		$ret = [];

		foreach($this->endpoints as $resource=>$apis){
			$ret += array_map(function($x){ return (object)$x['urls']; }, $apis);
		}

		return $dataSet ? $ret[$dataSet] : $ret;
	}

	function getDataSetFields($dataSet=NULL){
		$ret = [];

		foreach($this->endpoints as $resource=>$apis){
			foreach($apis as $title=>$apiData){
				$ret[$title] = array_map(function($x){ return (object)$x; }, $apiData['fields']);
			}
		}

		return $dataSet ? $ret[$dataSet] : $ret;
	}

	function queryAPI($dataSet, $options=array(), $array=TRUE){
		$apiKey = $this->API_Model->get_api_key('google_civic');
		$params = ['key' => $apiKey];

		foreach($options['filter'] as $search){
			$params += $search;
		}

		$query = $this->queryURL."/$dataSet?".http_build_query($params);
		die(file_get_contents($query));
	}

	function queryAPI_id($dataSet, $_id, $array=TRUE){
	}
}
