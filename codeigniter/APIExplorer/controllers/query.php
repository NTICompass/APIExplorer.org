<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Query extends CI_Controller {
	private $api_id,
			$api_info,
			$model;

	function __construct(){
		parent::__construct();

		$this->load->model('API_Model');
		$this->load->config('query_filters');
	}

	/*
	 * This lets me use URLS like:
	 * /query/openfema and /query/openfema/dataSetFields
	 */
	public function _remap($method, $params=[]){
		// The "method" is the api_id
		$this->api_id = $method;

		// Set the model to use for this API
		$this->api_info = $this->API_Model->get_api_info($this->api_id);
		if($this->api_info !== NULL){
			$this->model = $this->api_info->model_file;
			$this->load->model($this->model);
		}
		// Invalid API ID
		else{
			show_404();
		}

		// The 1st parameter is what method to call on that API
		// If there isn't one, then load the API's home page
		if(count($params) === 0){
			$this->api();
		}
		else{
			// Run the right methods with the right params
			$api_call = array_shift($params);
			if(method_exists($this, $api_call)){
				call_user_func_array([$this, $api_call], $params);
			}
			// Invalid method
			else{
				show_404();
			}
		}
	}

	/**
	 * Homepage for the API.  List all available data sets/API methods
	 */
	private function api(){
		$dataSets = $this->{$this->model}->getDataSets();

		$this->load->view('main_table', [
			'api_id' => $this->api_id,
			'logo' => $this->api_info->logo,
			'name' => $this->api_info->name,
			'dataSets' => $dataSets,
		]);
	}

	/**
	 * Show all available options for one particular data set/API method.
	 * This is where the query SQL is generated
	 */
	private function dataSetFields($dataSet){
		$fields = $this->{$this->model}->getDataSetFields($dataSet);
		$fieldNames = [];

		foreach($fields as $fieldInfo){
			$fieldNames[$fieldInfo->name] = $fieldInfo->title;
		}

		// TODO: Generalize filter functions
		$filters = $this->config->item('query_filters');

		$this->load->view('fields_table', [
			'api_id' => $this->api_id,
			'name' => $this->api_info->name,
			'dataSetInfo' => $this->{$this->model}->getDataSets($dataSet),
			'dataSetsFields' => $fields,
			'fieldNames' => $fieldNames,
			'filterFuncs' => form_dropdown('filterFuncs', $filters, 'eq', 'id="filterFuncs_clone" class="hide"'),
			'SQL_Query' => "SELECT :fields:\nFROM {$this->api_id}.{$dataSet}\nWHERE :query:\nORDER BY :sort:\nLIMIT :offset:, :limit:"
		]);
	}

	/**
	 * Run a query against the currently loaded API class
	 */
	private function ajax_queryAPI(){
		$dataSet = $this->input->post('dataSet');
		$fields = $this->input->post('fields');

		if($dataSet === FALSE || $fields === FALSE){
			$this->output->set_content_type('application/json')
				->set_output(json_encode([
					'error' => 'You are missing vital data!'
				]));
		}
		else{
			$filters = $this->input->post('filters');
			$filterCommand = null;
			if(is_array($filters)){
				$filterCommand = [];

				// $this->model::$filterFuncs is a syntax error
				$loadedAPI = $this->model;
				$filterFuncs = $loadedAPI::$filterFuncs;

				foreach($filters as $filterInfo){
					$function = $filterFuncs[$filterInfo['func']];

					// If it starts with a "!", that means the parameters are reversed
					if($filterInfo['func'][0] === '!'){
						$filterCommand[] = sprintf($function, $filterInfo['val'], $filterInfo['field']);
					}
					// Otherwise it's "field", "value"
					else{
						$filterCommand[] = sprintf($function, $filterInfo['field'], $filterInfo['val']);
					}
				}

				$filterCommand = implode(' and ', $filterCommand);
			}

			$data = $this->{$this->model}->queryAPI($dataSet, [
				'select' => is_array($fields) ? $fields : null,
				'orderby' => $this->input->post('sort') ?: null,
				'top' => $this->input->post('numRows') ?: null, # 100
				'skip' => $this->input->post('offset') ?: null, # 0
				'inlinecount' => $this->input->post('showCount') ?: null, # none
				'filter' => $filterCommand
			], FALSE);

			$this->output->set_content_type('application/json')
				->set_output($data);
		}
	}
}
