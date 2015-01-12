<?php


function doSqlQuery($sql) 
{
	return array();
}

/**
 * Return all operator stations
 * bus terminal, bus stop, pickup or dropoff points
 * 
 * @return Array of ['id' => station_id, 'name' => statation_name, 'adress' =>, 'description' =>,....city, province, lat,lng]
 * id, name - required fields, other is optional
 */
function getStationsList()
{
	$result = [];
	foreach (doSqlQuery("SELECT station_id, station_name FROM station") as $row) {
		$result[] = array(
			'id'   => $row['station_id'],
			'name' => $row['station_name'],
			//optional
			'adress'   => $row['adress'],
			'citu'     => $row['city'],
			'province' => $row['province'],
			'lat' => $row['lat'],
			'lng' => $row['lng'],
		);
	}
	return $result;
}

/**
 * Return all available(active, operable) routes from $stationId
 * 
 * @param int $stationId
 * 
 * @return Array [id, id, id, id]
 */
function getRouteList($stationId)
{
	$result = [];
	foreach (doSqlQuery("SELECT to_station_id FROM route WHERE from_station_id = {$stationId}") as $row) {
		$result[] = $row['to_station_id'];
	}
	return $result;
}

/**
 * Return all departures for $date
 * [time, class, price]
 * 
 * @param int    $fromId 
 * @param int    $toId
 * @param string $date Y-m-d
 * 
 * @return Array [time, class, price, (optional: distance, duration, stops[id,id,id])]
 */
function getSchedule($fromId, $toId, $date)
{
	$result = [];
	$query = "SELECT departure_time, bus_type, price FROM departure WHERE from_station_id = {$fromId} AND to_station_id={$toid} AND departure_date = {$date}";
	foreach (doSqlQuery($query) as $row) {
		$result[] = array(
			'time'  => $row['departure_time'], 
			'class' => $row['bus_type'], 
			'price' => $row['price'],
			//.. optional ...
			'seats_total'     => $row['seats_total'],
			'seats_available' => $row['seats_available'],
			'distance' => $row['distance'],
			'duration' => $row['duration'],
			'stops'    => array(/* staions id */]),
		);
	}
	return $result;
}


/**
 * Return bus seats map
 * 
 * @param int    $fromId 
 * @param int    $toId 
 * @param string $date Y-m-d
 * @param string $time h:i
 * @param string $class bus type identifier, can be optional
 * 
 * @return Array of seats identifiers
 */
function getSeatsMap($fromId, $toId, $date, $time, $class)
{
	$result = []; // init $result with all seats first
	$q = "SELECT * FROM booking LEFT JOIN passenger ON (passenger.booking_id = booking.booking_id)
	WHERE 
		bookig.from_station_id = {$fromId} 
	AND 
		bookig.to_station_id = {$toid}
	AND 
		booking.departure_date = '{$date}'
	AND 
		booking.departure_time = '{$time}'
	AND 
		booking.bus_type = '{$class}'";

	foreach (doSqlQuery($q) as $row) {
		$result[$row['seat']] = 'BOOKED';
	}
	return $result;
}

/**
 * Hold seats while booking and payment processing
 * 
 * @param int    $fromId 
 * @param int    $toId 
 * @param string $date departure date as Y-m-d
 * @param string $time departure time as h:i
 * @param mixed  $class bus type identifier 
 * @param array  $passengers [[first_name, second_name],...]
 * 
 * @return Array with unique booking ID, or null if something goes wrong
 */
function reservSeats($fromId, $toId, $date, $time, $class, Array $passengers)
{
	$bookingId = doSqlQuery("INSERT INTO booking SET 
		from_station_id = {$fromId}, 
		to_station_id   = {$toId}, 
		departure_time  = '{$time}', 
		departure_date  = '{$date}', 
		bus_type  = '{$class}'"
	);
	if ($bookingId) {
		doSqlQuery("INSERT INTO passenger (booking_id, first_name, second_name) VALUES({$bookingId}, '{$passengers[0]['first_name']}', '{$passengers[0]['last_name']}')");
	} else {
		return array(
			'booking_id' => $bookingId,
			'seccessful' => 1,
		);
	}
	return array(
		'booking_id' => null,
		'seccessful' => 0,
		'message'	 => '', // reason that booking not created
	);
}

/**
 * Set reserved bookig as confirmed(paid)
 * 
 * @param string $bookingId 
 * @return Array with confirmation results ['suscessful' => 1|0, 'message' => '']
 */
function confirmBooking($bookingId)
{
	$result = doSqlQuery("UPDATE booking SET status='CONFIRMED' WHERE booking_id = '{$bookingId}'");
	return array(
		'suscessful' => $result ? 1 : 0,
		'message'    => '', // error message if something goes wrong
	);
}

/**
 * Set reserved or confirmed bookig as canceled
 * 
 * @param string $bookingId
 * @return Array with cancellation results ['suscessful' => 1|0, 'message' => '']
 */
function cancelBooking($bookingId) 
{
	$result = doSqlQuery("UPDATE booking SET status='CANCELED' WHERE booking_id = '{$bookingId}'");	
	return array(
		'suscessful' => $result ? 1 : 0,
		'message'    => '', // error message if something goes wrong
	);
}

/**
 * Return booking info by id
 * 
 * @param string $bookingId 
 * @return Array booking details
 */
function getBookingDetail($bookingId)
{
	$res = doSqlQuery("SELECT * FROM booking WHERE booking_id = {$bookingId}");
	$passengers = doSqlQuery("SELECT * FROM passenger WHERE booking_id = {$bookingId}");
	// or use join
	if (!$res or !$pas) {
		// booking not found
		return null;
	}
	
	$result = array(
		'id'    => $res['booking_id'],
		'satus' => $res['status_id'],
		'from'  => $res['from_station_id'],
		'to'    => $res['to_station_id'],
		'date'  => $res['departure_date'], // Y-m-d
		'time'  => $res['departure_time'], // h:i
		'email' => $res['email'],
		'phone' => $res['phone'],
		'passengers' => array(),
	);

	foreach ($passengers as $pass) {
		$result['passengers'][] = 	array(
			'first_name'  => $pas[0]['first_name'], 
			'second_name' => $pas[0]['second_name'], 
			'seat'        => $pas[0]['seat'],
			'personal_id' => $pas[0]['id'] // optional
		);
	}

	return $result;
}