# Transport Company SDK for 12Go
There are 2 simple PHP files for easy integration of your company into [12go.asia](https://12go.asia) repository

 * api_contoller.php - API endpoint.
 * api_model.php - data access function you will need to extend.

####Integration
To integrate your inventory system into [12go.asia](https://12go.asia) you will have to extend API calls on your side, generate API key and send it to 12Go. You may simply place our 2 files to where you planned to have an API endpoint. **api_controller.php** file checks HTTP(s) requests, and calls certain data access functions. These functions are in the **api_model.php** file and all you have to do is to add specific code to access your database. 

For security, we have no access to this code.

####General
API calls are HTTP(s) GET calls
Each call must contain 3 mandatory parameters

  * method - API method to call
  * signature -  SHA1 hash code to sign the request
  * code - unique numeric sequence for each request

If you plan on using a different security approach, just let us know.

* **Station ID** - unique station identifier in your system.
* **Booking ID** - unique booking identifier in your system.
* **Class ID** - vehicle class to be used on a trip (VIP, VIP32, Express, etc)
* **Booking Status** - RESERVED (reserved during the booking process), CONFIRMED (seat paid), CANCELLED (booking canceled).
* **Time** - string as "h:i"
* **String** - string as "Y-m-d"

####Usage example
12Go will import your routes using **getStationsList** и **getRoutesList**. This allows 12Go go to make these available for the passengers in all devices/websites/apps connected to 12Go system.
**getSchedule** method will allow 12Go to check availability for a given date and time.
Passenger then will be able to choose the departure, reserve seats for the duration of booking process (**reserveSeats**), and get **Booking ID**.
For that given **Booking ID** it will be possible to **confirmBooking** or **cancelBooking**. 
Cancellation policy is then applied by the operator.

####API Methods

__getStationsList__ - returns station list. No input params. Response has to be a JSON:
```javascript
[
	[
        /* mandatory */    
    	"id"   : unq_station_id,
        "name" : station_name,
        /* optional */
        "description" : description_for_station,
        "alt_name"    : alternative_station_name /* local language */,
        "address"     : station_address,
        "lat"         : station_latitude,
        "lng"         : station_longitude
    ],
    /* other stations... */
]
```
Example: operator.site/api?method=getStationsList&code=1234&signature=sha1

__getRoutesList__ returns operator route list. No input params. Response has to be a JSON as is:
```javascript
[
	{
		"class" : coach_type,
		/* departure list with timing like h:i */
		"departures" : [time1, time2, time3],
		/* station list on the route, with duration in minutes between stations */
		"route" : {
			"0" : first_station_id,
			"duration from A to B" : next_station_id,
			/* other stations... */
			"duration from C to D"   : last_station_id
		},
		/* prices for different route spans */
		"price" : {
			"first_station_id-next_station_id" : price
			/* other route spans */
		}
	},
	/* other routes */
]
```
Пример: operator.site/api?method=getRoutesList&code=1234&signature=sha1

__getSchedule__ - returns departure list between two stations for a given date. Input params:

| Parameter     | Value	              |
| ------------- |---------------------|
| from_id       | unique station id   |
| to_id         | unique station id   |
| date          | departure date Y-m-d|
Response JSON:
```javascript
[
	{
		"time"  : departure_time,
		"class" : coach_type,
		"price" : price,
		"seats" : seats_count_available,
	},
	/* other departures... */
]
```
Example: operator.site/api?method=getSchedule&from_id=1&to_id=2&date=2015-10-10&code=1234&signature=sha1

__getSeatsMap__ - returns seat layout and taken seats for a given departure. Input params:

| Parameter     | Value               |
| ------------- |---------------------|
| from_id       | unique station id   |
| to_id         | unique station id   |
| date          | departure date Y-m-d|
| time          | departure time h:i  |
| class         | coach type          |
Response JSON:
```javascript
{
	"floor_id" : {
		"rows"   : count_of_seat_rows,
		"layout" : common_row_layout_pattern,
		/* custom seat rows */
		"custom" : {
			"row_num" : custom_layout_pattern,
		},
		"booked" : [seat_id, seat_id, seat_id],
	}
}
```
floor_id - vehicle floor or compartment, layout_pattern - string like 'xx xx', where 'x' - is a seat and space is a walk through.

Example: operator.site/api?method=getSeatsMap&from_id=1&to_id=2&time=10:00&date=2015-10-10&class=vip&code=1234&signature=sha1

__reserveSeats__ seat reservation for a given departure, for the duration of the booking process. Returns Booking ID or an error. Input parameters:

| Parameter     | Value                |
| ------------- |----------------------|
| from_id       | unique station id    |
| to_id         | unique station id    |
| date          | departure date Y-m-d |
| time          | departure time h:i   |
| email         | contact email        |
| phone         | contact phone        |
| passengers    | JSON                 |

Passenger definition is a JSON:
```javascript
[
	{
		"first_name"  : First,
		"last_name"   : Second,
		/* seat ID via getSeatsMap */
		"seat" : seat_id,
	},
	/* other passengers... */
]
```
Response is JSON:
```javascript
{
	// Unique Booking ID
    "booking_id" : unique_booking_id,
    // flag 0 or 1, depending on the booking call result
    "successful" : 1,
    // Error message in case there was one
    "message" : error_message,
}
```
Example: operator.site/api?method=reserveSeats&from_id=1&to_id=2&time=10:00&date=2015-10-10&code=1234&email=book@mail.com&phone=123456789&signature=sha1&passengers=[{"first_name":"Olaf","last_name":"Peterson","seat":"5A"},{"first_name":"Hanna","last_name":"Peterson","seat":"1B"}]

__confirmBooking__ booking confirmation after payment on 12Go side. Input is the Booking ID.

Response is JSON:

```javascript
{
    // flag 0 or 1, depending on the call result on operator side
    "successful" : 1,
    // Error message, if any
    "message" : message,
}
```
Example: operator.site/api?method=confirmBookig&code=1234&signature=sha1&booking_id=242234

__cancelBooking__  booking cancellation. input is the booking ID.

Response is JSON:

```javascript
{
    // flag 0 or 1, depending on the call result on operator side
    "successful" : 1,
    // Error message, if any
    "message" : message,
}
```
Example: operator.site/api?method=cancelBooking&code=1234&signature=sha1&booking_id=242234

__getBookingDetail__ returns booking details. Input is the Booking ID.

Response is JSON:

```javascript
{
 	"id"      : booking_id,
 	"status"  : booking_status,
	"from_id" : from_station_id,
  	"to_id"   : to_station_id,
  	"class"   : coach_type,
	"date"    : departure_date, // Y-m-d
  	"time"    : departure_time, // h:i
  	"email"   : contact_emil,
  	"phone"   : contact_phone, 
  	"passengers" : [
    	{
        	"first_name"  : First, 
        	"last_name"   : Last, 
            "seat"        : seat_id,
        },
		// other passengers...
    ]
}
```
Example: 
operator.site/api?method=getBookingDetail&code=1234&signature=sha1&booking_id=242234