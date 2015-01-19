<?php

require_once('api_model.php');

define(API_PARAM_FROM_ID,    'from_id');
define(API_PARAM_TO_ID,      'to_id');
define(API_PARAM_DATE,       'date');
define(API_PARAM_TIME,       'time');
define(API_PARAM_BUS_TYPE,   'bus_type');
define(API_PARAM_BOOKING_ID, 'booking_id');
define(API_PARAM_PHONE,      'phone');
define(API_PARAM_EMAIL,      'email');
define(API_PARAM_STATION_ID, 'station_id');
define(API_PARAM_PASSENGERS, 'passengers');

define(API_KEY, 'de0bgrez12!@ssde');

$API_METHOD_MAP = array(
	'getStationsList'   => array(),
	'getRoutesList'     => array(),
	'getRouteSchedule'  => array(API_PARAM_FROM_ID, API_PARAM_TO_ID, API_PARAM_DATE),
	'getSeatsMap'       => array(API_PARAM_FROM_ID, API_PARAM_TO_ID, API_PARAM_DATE, API_PARAM_TIME, API_PARAM_BUS_TYPE),
	'reserveSeats'      => array(API_PARAM_FROM_ID, API_PARAM_TO_ID, API_PARAM_DATE, API_PARAM_TIME, API_PARAM_BUS_TYPE, API_PARAM_EMAIL, API_PARAM_PHONE),
	'confirmBooking'    => array(API_PARAM_BOOKING_ID),
	'cancelBooking'     => array(API_PARAM_BOOKING_ID),
	'getBookingDetail'  => array(API_PARAM_BOOKING_ID),
);


/**
 * Validate params, check signature and process API request
 *
 * @param Array $req api request($_GET)
 * @return Array
 */
function callApiRequest(Array $req)
{
	global $API_METHOD_MAP;

	if (!array_key_exists('method', $req)) {
		return array('error' => 'API method must be specified');
	}
	$method = $req['method']; 

	if (!array_key_exists($method, $API_METHOD_MAP)) {
		return array('error' => 'API method not supported');
	}
	// check signature
	if (!array_key_exists('code', $req) or !array_key_exists('signature', $req)) {
		return array('error' => 'Signature not found');
	}
	
	if (!checkSignature($req['signature'], $method, API_KEY, $req['code'])) {
		return array('error' => 'Invalid signature');
	}
	
	$methodParams = [];
	foreach ($API_METHOD_MAP[$method] as $field) {
		if (!array_key_exists($field, $req)) {
			return array('error' => "Mandatory param {$field} not found");
		}
		if (!validateParamValue($field, $req[$field])) {
			return array('error' => "Param {$field} not valid");
		}
		$methodParams[$field] = $req[$field];
	}

	if ($method === 'reservSeats') {
		if (!array_key_exists(API_PARAM_PASSENGERS, $req)) {
			return array('error' => "Passengers information not found");
		}
		$req[API_PARAM_PASSENGERS] = json_decode($req[API_PARAM_PASSENGERS], true);
		if (!is_array($req[API_PARAM_PASSENGERS])) {
			return array('error' => "Passengers information invalid");
		}
		$methodParams[API_PARAM_PASSENGERS] = $req[API_PARAM_PASSENGERS];
	}
	
	$result = call_user_func_array($method, $methodParams);
	if (is_null($result)) {
		return array('error' => 'Method returns NULL');
	}
	return array('result' => $result);
}


/**
 * Check request signature
 * 
 * @param string $signature 
 * @param string $method
 * @param string $key 
 * @param string $code 
 * @return boolean
 */
function checkSignature($signature, $method, $key, $code)
{	
	return $signature === sha1($method . $key . $code);
}


/**
 * Validate api-request param
 * 
 * @param string $field 
 * @param string $value 
 * @return boolean
 */
function validateParamValue($param, $value)
{
	switch ($param) {
		case in_array($param, array(API_PARAM_TO_ID, API_PARAM_FROM_ID, API_PARAM_STATION_ID)):
			return is_numeric($value);
		case API_PARAM_BOOKING_ID:
			return (preg_match('/^[a-zA-Z0-9]{4,20}$/', $value) !== 0);
		case API_PARAM_TIME:
			return (preg_match('/^([01]{1}[0-9]{1}|2[0-3]{1}):[0-5]{1}[0-9]{1}$/', $value) !== 0);
		case API_PARAM_DATE:
			return @date('Y-m-d', strtotime($value)) === $value;
		case API_PARAM_EMAIL:
			return (boolean)filter_var($value, FILTER_VALIDATE_EMAIL);
		case API_PARAM_PHONE:
			return (preg_match('/^[\+]?[0-9]{6,15}$/', $value) !== 0);
		case API_PARAM_BUS_TYPE:
			return (preg_match('/^[a-zA-Z0-9 ]{1,30}$/', $value) !== 0);
		default:
			return false;
	}
}