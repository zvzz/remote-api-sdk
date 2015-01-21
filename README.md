# SDK для транспортных компаний
Два php-файла для простой интеграции транспортных компаний с [12go.asia](https://12go.asia)

 * api_contoller.php - Точка входа для обработки запроса к API.
 * api_model.php - Реализация функций доступа к данным.

####Интеграция
Для тогда что бы интегрировать вашу систему с [12go.asia](https://12go.asia) нужно реализовать на своей стороне вызовы API, сгенерировать свой ключ API и сообщить его. Вы можете просто подключить два наших файла к своему php-коду в том месте где вы предполагаете обрабатывать запросы к API. Файл **api_controller.php** содержит код обработки и проверки http-запросов, вызов соответствующих функций доступа к данным. Эти функции находятся в файле **api_model.php** в нем вам достаточно добавить свой код доступа к данным в соответствующих функциях. 

####Общие положения
Вызов API оператора проиходит по протоколу HTTP(S) с использованием метода GET.
Каждый запрос к API должен содержать три обязательных параметра:

  * method - метод API который нужно вызвать
  * signature -  sha1 хэш для подписи запроса
  * code - уникальная последовательность чисел для каждого запроса

Если вы будете использовать отличный от предложенного метод генерации подписи для запроса, сообщите об этом нам.

**Идентификатор станции** - произвольная срока однозначно пределяющая станцию оператора.
**Идентификатор брони** - произвольная строка однозначно определяющая бронь в системе оператора.
**Статус брони** - одно из значений вида: RESERVED(зарезервировано на время оплаты), CONFIRMED(бронь оплачена), CANCELLED(бронь отменена).
**Время** - строка в формате "h:i"
**Дата** - строка в формате "Y-m-d"

####Пример использования
Используя методы getStationsList и getRoutesList мы импортируем маршруты оператора в 12go.asia. Это позволит показывать эти маршруты нашим клиентам.
Метод getSchedule позволяет узнать наличе мест на на конкретную дату и время. Поле этого клиент может выбрать нужный рейс и зарезериваровать места на время оплаты(метод reserveSeats) и получить идентификатор брони. По этому идентификатору можно подтвердить бронь после оплаты(confirmBooking) или отменить(cancelBooking).

####Методы API

__getStationsList__ - возращает список станций с которыми работает оператор.
Входящих параметров нет. Ответ json-строка вида:
```javascript
[
	[
        /* обязательные поля */    
    	"id"   : "unq_station_id",
        "name" : station_name",
        /* опциональные поля */
        "description" : "description_for_station",
        "alt_name" : "alternative_station_name",
        "address"  : "adress_of_station",
        "lat" : "station_latitude",
        "lng" : "station_longitude"
    ]
    /* остальные станции... */
]
```
Пример:  operator.site/api?method=getStationsList&code=1234&signature=sha1

__getRoutesList__ возвращает список маршрутов с которыми работает оператор. Входящих параметров нет. Ответ json-строка вида:
```javascript
[
	{
		"class" : "coach_type",
		/* расписание отправлений формате h:i */
		"departures" : ["time1", "time2", "time3"],
		/* список станций на маршруте */
		"route" : {
			"0" : "first_station_id",
			"duration from first to second" : "second_station_id",
			/* транзитные станции... */
			"duration from first to last"   : "last_station_id"
		},
		/* цены на участки маршрут */
		"price" : {
			"first_station_id-second_station_id" : "price"
			/* цены на другие участки маршрута */
		}
	},
	/* другие маршруты */
]
```
Пример: operator.site/api?method=getRoutesList&code=1234&signature=sha1

__getSchedule__ - возвращает список отправлений между двумя станциями на определенную дату. Входящие параметры:

| Параметр      | Значение             |
| ------------- |---------------------|
| from_id       | unique station id   |
| to_id         | unique station id   |
| date          | departure date Y-m-d|
Ответ json-строка вида:
```javascript
[
	{
		"time"  : "departure time",
		"class" : "coach type",
		"price" : "price of route",
		"seats" : "num of available seats",
	},
	/* остальные отправления... */
]
```
Пример: operator.site/api?method=getSchedule&from_id=1&to_id=2&date=2015-10-10&code=1234&signature=sha1

__getSeatsMap__ - возращает схему сидений и списрк занятых мест на конкретном райсе автобуса. Входящие параметры:

| Параметр      | Значение            |
| ------------- |---------------------|
| from_id       | unique station id   |
| to_id         | unique station id   |
| date          | departure date Y-m-d|
| time          | departure time h:i  |
| class         | coach type          |
Ответ json-строка вида:
```javascript
{
	"floor_id" : {
		"rows" : "count of seat rows",
		"layout" : "layout_pattern",
		/* ряды с раскладкой отличной от основной */
		"custom" : {
			"row_num" : "layout_pattern",
		},
		"booked" : {
			"seat1_id" : "passenger id",
			"seat2_id" : "passenger id",
			"seat3_id" : "passenger id",
		}
	}
}
```
floor_id - номер этажа автобуса, layout_pattern - строка вида 'xx xx', в которой 'x' - ряд сидений в автобусе а пробельный символ - проход между креслами.

Пример: operator.site/api?method=gete&from_id=1&to_id=2&time=10:00&date=2015-10-10&class=vip&code=1234&signature=sha1

__reserveSeats__ бронирование мест в конкретном рейсе на время оплаты, возвращает индентификатор брони или сообщение об ошибке. Входящие параметры:

| Параметр      | Значение            |
| ------------- |---------------------|
| from_id       | unique station id   |
| to_id         | unique station id   |
| date          | departure date Y-m-d|
| time          | departure time h:i  |
| email         | contact email       |
| phone         | contact phone       |
| passengers    | json lsit of passengers  |
Строка описывающая пассажиров это JSON вида:
```javascript
[
	{
		"first_name"  : "First",
		"last_name" : "Second",
		/* идентификатор места, полученный методом getSeatsMap */
		"seat" : "seat_id",
	},
	/* остальные пассажиры... */
]
```
Ответ json-объект вида:
```javascript
{
	// Уникальный идентификатор брони
    "booking_id" : "unique_booking_id",
    // флаг 0 или 1, в зависимости от того удалось ли зарезервировать места
    "successful" : 1,
    // Сообщение об ошибке если бронь не создана
    "message" : "error_message",
}
```
Пример вызова: operator.site/api?method=getRouteSchedule&from_id=1&to_id=2&time=10:00&date=2015-10-10&code=1234&email=book@mail.com&phone=123456789&signature=sha1&passengers=[{"first_name":"Olaf","last_name":"Peterson","seat":"5A"},{"first_name":"Hanna","last_name":"Peterson","seat":"1B"}]

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
Пример вызова: operator.site/api?method=confirmBookig&code=1234&signature=sha1&booking_id=242234

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
Пример вызова: operator.site/api?method=cancelBooking&code=1234&signature=sha1&booking_id=242234

__getBookingDetail__ возвращает детали бронирования. На вход передается идентификатор брони.

Ответ json-объект вида:

```javascript
{
 	"id"      : "booking_id",
 	"satus"   : "booking_status",
	"from_id" : "from_station_id",
  	"to_id"   : "to_station_id",
  	"class"   : "coach_type,
	"date"    : "departure_date", // Y-m-d
  	"time"    : "departure_time", // h:i
  	"email"   : "contact_emil",
  	"phone"   : "contact_phone", 
  	"passengers" : [
    	{
        	"first_name"  : "First", 
        	"last_name"   : "Last", 
            "seat"        : "seat_id",
        },
		// other passengers...
    ]
}
```
Пример: 
operator.site/api?method=getBookingDetail&code=1234&signature=sha1&booking_id=242234