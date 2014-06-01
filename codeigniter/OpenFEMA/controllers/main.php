<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends CI_Controller {
	function __construct(){
		parent::__construct();
		$this->load->model('FEMA_Model');
	}

	public function index(){
		$this->load->view('main_table', [
			'dataSets' => $this->FEMA_Model->getDataSets()
		]);
	}

	public function dataSetFields($dataSet){
		$fields = $this->FEMA_Model->getDataSetFields($dataSet);
		$fieldNames = [];

		foreach($fields as $fieldInfo){
			$fieldNames[$fieldInfo->name] = $fieldInfo->title;
		}

		$filters = [
			'eq' => 'Equal',
			'ne' => 'Not Equal',
			'gt' => 'Greater Than',
			'ge' => 'Greater Than or Equal',
			'lt' => 'Less Than',
			'le' => 'Less Than or Equal',
			'substringof' => 'Contains',
			'endswith' => 'Ends With',
			'startswith' => 'Starts With',
			'not_substringof' => 'Does not Contain',
			'not_endswith' => 'Does not End With',
			'not_startswith' => 'Does not Start With'
		];

		$this->load->view('fields_table', [
			'dataSetInfo' => $this->FEMA_Model->getDataSets($dataSet),
			'dataSetsFields' => $fields,
			'fieldNames' => $fieldNames,
			'filterFuncs' => form_dropdown('filterFuncs', $filters, 'eq', 'id="filterFuncs_clone" class="hide"')
		]);
	}

	public function ajax_queryAPI(){
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

				$filterFuncs = [
					'eq' => "%s eq '%s'",
					'ne' => "%s ne '%s'",
					'gt' => "%s gt '%s'",
					'ge' => "%s ge '%s'",
					'lt' => "%s lt '%s'",
					'le' => "%s le '%s'",
					'substringof' => "substringof(%s, '%s')",
					'endswith' => "endswith(%s, '%s')",
					'startswith' => "startswith(%s, '%s')",
					'not_substringof' => "not substringof(%s, '%s')",
					'not_endswith' => "not endswith(%s, '%s')",
					'not_startswith' => "not startswith(%s, '%s')"
				];

				foreach($filters as $filterInfo){
					$filterCommand[] = sprintf($filterFuncs[$filterInfo['func']], $filterInfo['field'], $filterInfo['val']);
				}

				$filterCommand = implode(' and ', $filterCommand);
			}

			$data = $this->FEMA_Model->queryAPI($dataSet, [
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
