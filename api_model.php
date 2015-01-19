<?php

/**
 * Returns all operator stations
 * terminals, stops, pickup or dropoff points
 * 
 * @return Array of ['id' => station_id, 'name' => statation_name, 'adress' =>, 'description' =>,....city, province, lat,lng]
 * id, name - required fields, other is optional
 */
function getStationsList()
{
	return array(
		array('id' => 9,  'name' => 'Morchit',    'description' => 'Bangkok Northeastern Bus Terminal'),
		array('id' => 13, 'name' => 'Chiang Mai', 'description' => 'Bus Station Wat Ket Chiang Mai'),
		array('id' => 41, 'name' => 'Lampang',    'description' => 'Jant Surin Bus Terminal'),
		array('id' => 72, 'name' => 'Chiang Rai', 'description' => 'Chiang Rai bus station'),
	);
}

/**
 * Return all available(active, operable) routes
 * 
 * @return Array see doc for detailed description of array structure
 */
function getRoutesList()
{
	return array(
		// Bangkok(Mo Chit) -> Lampang -> Chiang Mai
		array('class' => 'VIP', 'departures' => array('06:00', '11:00'),
			'route' => array(0 => 9, 600 => 41, 780 => 13), 
			'price' => array('9-41' => '620', '9-13' => '650'),
		),
		// Chiang Mai -> Lampang  -> Bangkok(Mo Chit)
		array('class' => 'VIP', 'departures' => array('08:00', '15:00'),
			'route' => array(0 => 13, 180 => 41, 780 => 9),
			'price' => array('13-9' => '650.20'),
		),
		// Chiang Rai -> Chiang Mai
		array('class' => 'Executive', 'departures' => array('08:00', '10:00', '16:00', '18:00'), 
			'route' => array(0 => 72, 200 => 13), 
			'price' => array('72-13' => '220')
		),
		// Chiang Mai -> Chiang Rai
		array('class' => 'Executive', 'departures' => array('09:00', '11:00', '15:00', '17:00'), 
			'route' => array(0 => 13, 200 => 72),
			'price' => array('13-72' => '220'),
		),
	);
}

/**
 * Return all departures for $date
 * with seats
 * [time, class, price]
 * 
 * @param int    $fromId 
 * @param int    $toId
 * @param string $date Y-m-d
 * 
 * @return Array [[departure time, class, price, seats avaible],..]
 */
function getRouteSchedule($fromId, $toId, $date)
{
	return array(
		// Bankkok -> Chiang Mai
		array('06:00', 'VIP', 650, 28),
		array('11:00', 'VIP', 650, 13),
	);
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
	// Bankkok -> Chiang Mai at '11:00', VIP-class
	return array(
		//first floor =>
		1 => array(
			'rows'   => 10,
			'layout' => '00 00',
			'custom' => array(
				//row => map
				5 => 'xx00',
				6 => 'xx00',
			),
			'booked' => array(
				// seat_code => passenger type
				'1A' => 'MALE',
				'2A' => 'FEMALE',
				'3A' => 'SOLDER',
				'4A' => 'MONK',
				'1B' => 'MALE',
				'2B' => 'MALE',
				'3B' => 'MONK',
				'4B' => 'MONK',
			),
		),
	);
}

/**
 * Hold seats while booking and payment processing
 * 
 * @param int    $fromId 
 * @param int    $toId 
 * @param string $date departure date as Y-m-d
 * @param string $time departure time as h:i
 * @param mixed  $class bus type identifier
 * @param string $email 
 * @param string $phone for contact
 * @param array  $passengers [[first_name, second_name, seat_id],...]
 * 
 * @return Array with unique booking ID, or null if something goes wrong
 */
function reservSeats($fromId, $toId, $date, $time, $class, $email, $phone, Array $passengers)
{
	return array(
		'booking_id' => 'booking', // or null if booking unsuccessful
		'successful' => 1,  // 1|0 successful or not
		'message'	 => '', // reason that booking not created or empty string
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
	return array(
		'suscessful' => 1,  // 1|0 successful or not
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
	return array(
		'suscessful' => 1,  // 1|0 successful or not
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
	// Bangkok -> Chian Mai at 11:00 2015-02-10 for 2 person
	return array(
		'id'    => 'booking_id',
		'satus' => 'status_id', // RESERVED | PAID | CONFIRMED | CANCELLED
		'from'  => 9,
		'to'    => 13,
		'class' => 'VIP',
		'date'  => '2015-02-10', // Y-m-d
		'time'  => '11:00', // h:i
		'email' => 'olaf.peter@gmail.com',
		'phone' => '+660925453322', 
		'passengers' => array(
			array(
				'first_name'  => 'Olaf',
				'second_name' => 'Peterson',
				'seat' => '1A',
			),
			array(
				'first_name'  => 'Hanna',
				'second_name' => 'Peterson',
				'seat' => '1B',
			),
		),
	);

}