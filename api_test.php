<?php

require_once('api_model.php');
require_once('api_controller.php');

$signature1 = sha1('getStationsList' . API_KEY . '1');
$signature2 = sha1('getRouteList' . API_KEY . '2');
$signature3 = sha1('getSchedule' . API_KEY . '3');

//exit();

var_dump(validateParamValue(API_PARAM_PHONE, '123'));
var_dump(validateParamValue(API_PARAM_PHONE, '123a'));
var_dump(validateParamValue(API_PARAM_PHONE, '+66123123'));
var_dump(validateParamValue(API_PARAM_PHONE, '1231244123'));


var_dump(validateParamValue(API_PARAM_EMAIL, 'aad@ddd'));
var_dump(validateParamValue(API_PARAM_EMAIL, 'asd@asdd.12'));
var_dump(validateParamValue(API_PARAM_EMAIL, 'mail@test.dom'));
var_dump(validateParamValue(API_PARAM_EMAIL, 'mail@test.ru'));


var_dump(validateParamValue(API_PARAM_TIME, '10:10'));
var_dump(validateParamValue(API_PARAM_TIME, '12:10'));
var_dump(validateParamValue(API_PARAM_TIME, '24:10'));
var_dump(validateParamValue(API_PARAM_TIME, '10:61'));


var_dump(validateParamValue(API_PARAM_DATE, '2015-10-10'));
var_dump(validateParamValue(API_PARAM_DATE, '2015-12-12'));
var_dump(validateParamValue(API_PARAM_DATE, '2015-10-33'));
var_dump(validateParamValue(API_PARAM_DATE, '2015-13-10'));


var_dump(callApiRequest(array(
	'method' => 'getStationsList', 
	'code'   => '1',
	'signature' => $signature1
))); 

var_dump(callApiRequest(array(
	'method'  => 'getRouteList', 
	'from_id' => 1,
	'code'    => '2',
	'signature' => $signature2
)));

var_dump(callApiRequest(array(
	'method'  => 'getSchedule', 
	'from_id' => 1,
	'to_id'   => '1',
	'date'    => '2015-12-02', 
	'code'    => '3',
	'signature' => $signature3
)));

var_dump(callApiRequest(array('method' => 'getSeatsMap', 'from_id' => 1, 'to_id' => '1', 'date' => '2015-12-02', 'time' => '10:20', 'bus_type' => ' a sd1 2dd s2')));
var_dump(callApiRequest(array(
	'method'   => 'reservSeats', 
	'from_id'  => 1, 
	'date'     => '2015-12-02',
	'time'     => '10:20',
	'to_id'    => '1123',
	'bus_type' => ' a sd1 2dd s2',
	'passengers' => json_encode(array(array('first_name' => 'John', 'second_name' => 'Smith'),array('first_name' => 'Bob', 'second_name' => 'Chop'))),
)));
var_dump(callApiRequest(array('method' => 'confirmBooking', 'booking_id' => 'h247b45c20')));
var_dump(callApiRequest(array('method' => 'cancelBooking', 'booking_id' => 'h247b45c20')));
var_dump(callApiRequest(array('method' => 'getBookingDetail', 'booking_id' => 'h247b45c20')));
