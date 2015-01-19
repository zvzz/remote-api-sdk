# SDK для транспортных компаний
Два php-файла для простой интеграции транспортных компаний с [12go.asia](https://12go.asia)

 * api_contoller.php - Точка входа для обработки запроса к API.
 * api_model.php - Релизация функций доступа к данным.



__getStationsList__
 - возращает список станций с которыми работает оператор.
Входящих параметров нет.
Формат json-список вида:

```php
[
	[
        /* обязательные поля */    
    	'id'   : unq_station_id,
        'name' : station_name,
        /* опциональные поля */
        'description' : description_for_station,
        'alt_name' : alternative_station_name,
        'address'  : adress_of_station,
        'lat' : station_latitude,
        'lng' : station_longitude
    ]
    /* other stations... */
]
```
Пример вызова: operator.site/api?method=getStationsList&code=1234&signature=a94a8fe5ccb19ba61c4c0873d391e987982fbbd3

__getRoutesList__ возвращает список маршрутов с которыми работает оператор. Входящих параметров нет. Формат ответа массив вида:
```php
array(
	array(
		'class' => coach_type,
        /* list of departures from first station (time as h:i) */
        'departures' => array(time1, time2, time3),
        'route' => array(
        	station1_id => 0,
            /* other transit stations... */
            stationN_id => duration_from_first_route_station_in_minutes,
        ),
        'price' => array(
        	first_station_id-second_station_id => price1,
            /* other prices... */
        	first_station_id-last_station_id => priceN,
        ),
	),
    /* other routes */
)
```
Пример вызова: operator.site/api?method=getRoutesList&code=1234&signature=4a8fe5ccb19ba61c4c0873d391e987982fbbd3

__getRouteSchedule__ - возвращает список отправлений между двумя станциями на определенную дату. Входящие параметры:

| Параметр      | Значение            |
| ------------- |---------------------|
| from_id       | unique station id   |
| to_id         | unique station id   |
| date          | departure date Y-m-d|
Формат ответа массив вида:
```php
array(
	array(deparure_time, class, price, seats_available),
    /* ... */
	array(deparure_time, class, price, seats_available),
)
```
Пример вызова: operator.site/api?method=getRouteSchedule&from_id=1&to_id=2&date=2015-10-10&code=1234&signature=4a8fe5ccb19ba61c4c0873d391e987982fbbd3

__getSeatsMap__ - возращает схему сидений и списрк занятых мест на конкретном райсе автобуса. Входящие параметры:

| Параметр      | Значение            |
| ------------- |---------------------|
| from_id       | unique station id   |
| to_id         | unique station id   |
| date          | departure date Y-m-d|
| time          | departure time h:i  |
| class         | coach type          |
Ответ json-объект вида:
```php
array(
	floor_id => array(
    	'rows'   => count_of_seat_rows,
        'layout' => layout_pattern
        /* list of rows with custom layout*/
        'custom' => array(
        	row_num => layout_pattern,
        ),
        'booked' => array(
        	seat1_id => passanger_id,
            seat2_id => passanger_id,
            seat3_id => passanger_id,
        ),
    ),
)
```
Пример вызова: operator.site/api?method=getRouteSchedule&from_id=1&to_id=2&time=10:00&date=2015-10-10&code=1234&signature=4a8fe5ccb19ba61c4c0873d391e987982fbbd3

layout_pattern - строка вида 'xx xx', в которой 'x' - ряд сидений в автобусе а пробельный символ - проход между креслами.

__reserveSeats__ резервация мест в конкретном рейсе на ремя оплаты, возвращает индетификатор брони:

| Параметр      | Значение            |
| ------------- |---------------------|
| from_id       | unique station id   |
| to_id         | unique station id   |
| date          | departure date Y-m-d|
| time          | departure time h:i  |
| email         | contact email       |
| phone         | contact phone       |
| passengers    | json lsit of passengers  |
Ответ json-объект вида:
```javascript
{
	// Уникальный идентификатор брони
    'booking_id' : unique_booking_id,
    // флаг 0 или 1, в зависимости от того удалось ли зарезервивать места
    'successful' : 1,
    // Сообщение об ошибке если бронь не создана
    'message' : message,
}
```
Пример вызова: operator.site/api?method=getRouteSchedule&from_id=1&to_id=2&time=10:00&date=2015-10-10&code=1234&email=book@mail.com&phone=123456789&signature=4a8fe5ccb19ba61c4c0873d391e987982fbbd3&passengers=[{"first_name":"Olaf","second_name":"Peterson","seat":"5A"},{"first_name":"Hanna","second_name":"Peterson","seat":"1B"}]

__confirmBooking__ подтверждение ранее совершенной брони, после оплаты. На вход передается идентификатор брони.

Ответ json-объект вида:

```javascript
{
    // флаг 0 или 1, в зависимости от того удалось ли зарезервивать места
    'successful' : 1,
    // Сообщение об ошибке если бронь не создана
    'message' : message,
}
```
Пример вызова: operator.site/api?method=confirmBookig&code=1234&signature=a94a8fe5ccb19ba61c4c0873d391e987982fbbd3&booking_id=242234


__cancelBooking__  отмена ранее совершенной брони, после оплаты. На вход передается идентификатор брони.

Ответ json-объект вида:

```javascript
{
    // флаг 0 или 1, в зависимости от того удалось ли отменить бронь
    'successful' : 1,
    // Сообщение об ошибке если бронь отменить не удалось
    'message' : message,
}
```
Пример вызова: operator.site/api?method=cancelBooking&code=1234&signature=a94a8fe5ccb19ba61c4c0873d391e987982fbbd3&booking_id=242234

__getBookingDetail__ возвращает дети бронирования. На вход передается идентификатор брони

Ответ json-объект вида:

```javascript
{
 	'id'      : booking_id,
 	'satus'   : booking_status,
	'from_id' : 9,
  	'to_id'   : 13,
  	'class'   : coach_type,
	'date'    : departure_date, // Y-m-d
  	'time'    : departure_time, // h:i
  	'email'   : contact_emil,
  	'phone'   : contact_phone, 
  	'passengers' : [
    	{
        	'first_name'  : passenger_first_name, 
        	'second_name' : passenger_second_name, 
            'seat'        : seat_id,
        },
		// other passengers...
    ]
}
```
Пример вызова: operator.site/api?method=getBookignDetail&code=1234&signature=a94a8fe5ccb19ba61c4c0873d391e987982fbbd3&booking_id=242234




