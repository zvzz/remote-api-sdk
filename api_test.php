<?php

require_once('api_model.php');
require_once('api_controller.php');

/**
 * Validate response getBookingDetails
 * throw exception if error found
 * @param Array $res
 * @return null
 */
function validateBookingDetails($res)
{
	$fields = array('id', 'satus', 'from', 'to', 'class', 'date', 'time', 'email', 'phone');
	if (!isset($res['result']) or !is_array(($res['result']))) {
		throw new Exception("Result not found in " . json_encode($res));
	}
	$res = $res['result'];
	foreach ($fields as $field) {
		if (!isset($res[$field])) {
			throw new Exception(" '{$field}' not found in " . json_encode($res));		
		}
	}
	if (!isset($res['passengers']) or !is_array(($res['passengers']))) {
		throw new Exception("Passengers information not found in " . json_encode($res));
	}
	if (!validatePassengers($res['passengers'])) {
		throw new Exception("Passengers information not valid in " . json_encode($res['passengers']));
	}
}

/**
 * Validate response for booking methods
 * throw exception if error found
 * @param Array $response
 * @return null
 */
function validateSuccessful(Array $res)
{
	if (!isset($res['result']) or !is_array(($res['result']))) {
		throw new Exception("Result not found in " . json_encode($res));
	}
	if (!isset($res['result']['successful'])) {
		throw new Exception("Successful flag not found in " . json_encode($res));
	}
}

/**
 * Validate getStationsList response structure
 * throw exception if error found
 * @param Array $list of station
 * @return null
 */
function validateStationsList(Array $list)
{
	foreach ($list as $station) {
		if (isset($station['id']) and isset($station['name'])) {
			continue;
		}
		throw new Exception("Invalid station structure: " . json_encode($station));
	}
}

/**
 * Validate getRoutesList response structure
 * throw exception if error found
 * @param Array $list of routes
 * @return null
 */
function validateRoutesList(Array $list)
{
	$fields = array('class', 'departures', 'route', 'price');
	foreach ($list as $route) {
		foreach ($fields as $field) {
			if (!isset($route[$field])) {
				throw new Exception("Invalid route structure");	
			}
			if ($field == 'class') {
				continue;
			}
			if (!is_array($route[$field]) or count($route[$field]) === 0) {
				throw new Exception("Invalid route '{$field}' value");
			}
		}
	}
}

/**
 * Validate getSeatsMap response structure
 * throw exception if error found
 * @param Array $map seats map layout
 * @return null
 */
function validateSeatsMap(Array $map) 
{
	foreach ($map as $id => $floor) {
		foreach(array('rows', 'layout', 'custom', 'booked') as $field) {
			if (!isset($floor[$field])) {
				throw new Exception("Invalid foor structure: " . json_encode($floor));
			}
		}		
	}
}

/**
 * Validate getScheduler response structure
 * throw exception if error found
 * @param Array $map list of trips
 * @return null
 */
function validateSchedule(Array $list) 
{
	foreach ($list as $trip) {
		foreach(array('time', 'class', 'price', 'seats') as $field) {
			if (!isset($trip[$field])) {
				throw new Exception("Invalud schedule structure");
			}
		}
	}
	
}

foreach (array('123', '123a', 's123a') as $value) {
	if (validateParamValue(API_PARAM_PHONE, $value)) {
		throw new Exception("Invalid phone validation result for {$value}");
	}
}
foreach (array('+66123123', '1231244123', '+78083124423') as $value) {
	if (!validateParamValue(API_PARAM_PHONE, $value)) {
		throw new Exception("Invalid phone validation result for {$value}");
	}
}

foreach (array('aad@ddd', 'asd@asdd.12', 'asdwedds') as $value) {
	if (validateParamValue(API_PARAM_EMAIL, $value)) {
		throw new Exception("Invalid email validation result for {$value}");
	}
}
foreach (array('me@test.com', 'book@gmail.com', 'valid@email.com') as $value) {
	if (!validateParamValue(API_PARAM_EMAIL, $value)) {
		throw new Exception("Invalid email validation result for {$value}");
	}	
}

foreach (array('09:00', '10:00', '12:40', '22:10') as $value) {
	if (!validateParamValue(API_PARAM_TIME, $value)) {
		throw new Exception("Invalid time validation result for {$value}");
	}
}
foreach (array('2:13', '11:3', '12:61', '25:40') as $value) {
	if (validateParamValue(API_PARAM_TIME, $value)) {
		throw new Exception("Invalid time validation result for {$value}");
	}	
}

foreach (array('2015-10-10', '2015-12-10', '2015-10-20', '2015-05-05') as $value) {
	if (!validateParamValue(API_PARAM_DATE, $value)) {
		throw new Exception("Invalid date validation result for {$value}");
	}
}
foreach (array('10-10-1999', '2015-15-15', '15/10/10', '15/20/05') as $value) {
	if (validateParamValue(API_PARAM_DATE, $value)) {
		throw new Exception("Invalid time validation result for {$value}");
	}	
}

$code = mt_rand(1, 10000);
$res  = callApiRequest(array(
	'method'    => 'getStationsList', 
	'code'      => $code,
	'signature' => sha1('getStationsList' . API_KEY . $code),
)); 

validateStationsList($res['result']);

$code = mt_rand(1, 10000);
$res  = callApiRequest(array(
	'method'    => 'getRoutesList', 
	'code'      => $code,
	'signature' => sha1('getRoutesList' . API_KEY . $code)
));

validateRoutesList($res['result']);

$code = mt_rand(1, 10000);
$res  = callApiRequest(array(
	'method'  => 'getSchedule', 
	'from_id' => 9,
	'to_id'   => 13,
	'date'    => '2015-12-02', 
	'code'    => $code,
	'signature' => sha1('getSchedule' . API_KEY . $code)
));

validateSchedule($res['result']);

$code = mt_rand(1, 10000);
$res  = callApiRequest(array(
	'method'    => 'getSeatsMap',
	'from_id'   => 9, 
	'to_id'     => 13, 
	'date'      => '2015-12-02', 
	'time'      => '11:00', 
	'class'     => 'VIP',
	'code'      => $code,
	'signature' => sha1('getSeatsMap' . API_KEY . $code)
));

validateSeatsMap($res['result']);

$code = mt_rand(1, 10000);
$res  = callApiRequest(array(
	'method'     => 'reserveSeats', 
	'from_id'    => 1, 
	'date'       => '2015-12-02',
	'time'       => '11:00',
	'to_id'      => 13,
	'class'      => 'VIP',
	'code'       => $code,
	'email'		 => 'bob@mail.com',
	'phone'		 => '+669001002020',
	'signature'  => sha1('reserveSeats' . API_KEY . $code),
	'passengers' => json_encode(array(
		array('first_name' => 'John', 'last_name' => 'Smith', 'seat' => '1C'),
		array('first_name' => 'Bob',  'last_name' => 'Chop',  'seat' => '2C')
	)),
));

validateSuccessful($res);

$code = mt_rand(1, 10000);
$res  = callApiRequest(array(
	'method'     => 'confirmBooking',
	'booking_id' => 'h247b45c20',
	'code'       => $code,
	'signature'  => sha1('confirmBooking' . API_KEY . $code),
));

validateSuccessful($res);

$code = mt_rand(1, 10000);
$res  = callApiRequest(array(
	'method'     => 'cancelBooking', 
	'booking_id' => 'h247b45c20', 
	'code'       => $code, 
	'signature'  => sha1('cancelBooking' . API_KEY . $code),
));

validateSuccessful($res);

$code = mt_rand(1, 10000);
$res  = callApiRequest(array(
	'method'     => 'getBookingDetail', 
	'booking_id' => 'h247b45c20',
	'code'       => $code, 
	'signature'  => sha1('getBookingDetail' . API_KEY . $code),
));

validateBookingDetails($res);