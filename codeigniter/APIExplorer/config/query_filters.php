<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// These filter commands are from OpenFEMA's API
// TODO: Abstract these out for different APIs
$config['query_filters'] = [
	'eq' => 'Equal', # a = b
	'ne' => 'Not Equal', # a != b
	'gt' => 'Greater Than', # a > b
	'ge' => 'Greater Than or Equal', # a >= b
	'lt' => 'Less Than', # a < b
	'le' => 'Less Than or Equal', # a <= b
	'!substringof' => 'Contains', # a LIKE CONCAT('%', b, '%')
	'endswith' => 'Ends With', # a LIKE CONCAT('%', b)
	'startswith' => 'Starts With', # a LIKE CONCAT(b, '%')
	'!not_substringof' => 'Does not Contain', # a NOT LIKE CONCAT('%', b, '%')
	'not_endswith' => 'Does not End With', # a NOT LIKE CONCAT('%', b)
	'not_startswith' => 'Does not Start With' # a NOT LIKE CONCAT(b, '%')
];
