# Assignment for the job position of the Back-end Engineer Mid/Senior

**A few words about the project**
My name is Christos Yiakoumettis and this is an assignment for the job position of the Back-end Engineer Mid/Senior.
I completed this assignment having in mind the skills and knowledge that you require for this job position.
I designed and implemented a API using the next tech stacks
* Docker, 
* Git
* PHP, 
* Slim framework 4.0
* PostgreSQL,
* Redis
* Papertrail for logging incoming requests
* Microservice, CLI for migrating external data


**Technical Infrastructure **
The whole project is dockerized. Virtual services such as nginx web server image, PHP 8.1, Redis and PosgreSQL 11.1 has been set up on docker.


**Running the project **
In order to replicate and run this project you have to perform the next commands from a terminal or console
* **docker-composer up -d** -- setup services via docker 
* **(winpty) docker exec -it api-php-fpm-1 bash -- perform this command only if you need active PHP8.1. In this way the next command will be executed from PHP container od docker.
* **composer update -o** -- install all the required PHP libraries of the project
* ** cd /application/app/cli && php import_data.php -- imports migration data from /data/ship_positions.json file to postgreSQL database
* **check .env file of the project to configure papertrail third party logging service** -- you can modify other environmental settings too according to your own needs. On .env file you can find settings about migration .json file path location. The other settings are been configured to work with dockerized services.
* **see API in action - open [GET] http://localhost/v1/vessels endpoint with te proper raw data.

**Service Usage**
This service contains a single endpoint (/v1/vessels). It takes a json as row data input. A complete example is presented on the next lines.
Data can be filtered the next fields as an fields
* **mmsi** - mandatory: no, it an array of the unique vessel identifiers. It can be one or more.
* **points** - mandatory: no, it presents two points that are required to shape a quadrilateral by providing the upper left corner and the lower right or by providing the lower left and the upper right corner
* **time** - mandatory: no, it is consisted by two timestamps for calculating the time interval that matches the vessel timestamp data. It can be set by two variables one defining the initial time stamp (ts_since) and another one defining the final timestamp (ts_to)

All the filtering criteria can match together at the same time. If a field criterion is missing from the input, then it is not been considered on the results set. If no filter criteria are provided to the API endpoint a response with code status 415 (unsupported media type) is returned by the API.
Besides fileting criteria, the content type of the response can be defined be the key variable **ContentType** of input json. API supports three types of output. If no content type is defined by the input the defualt one (JSON) is been selected by the API
* JSON (application/json). it is the default one
* XML (application/xml)
* CSV (text/csv)

input JSON example:
{"filters":{"mmsi":[247039300, 311040700], "points":[{"lon":14.36933, "lat":40.82278},{"lon":18.87793, "lat":42.75178}], "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"application/json"}

**Data integration process**
Data integration process is taking please by a microservice. It has to run from cli due to security and performance issues. The script is stores within app/cli folder and it not accessible from web (neither data file does).
The provided data field in json format is been parsed by the microservice and store all the retrieved information in a database schema of PostgreSQL. The data base model is presented below.
* id - int8
* mmsi - int8
* status - int2
* stationid -int2
* speed - int2
* position - point / stores longitude and latitude of data in a single point
* course - int2
* heading - int2
* rotation - varchar
* timestamp - timestamptz
* created_on - timestamptz

**Service Technical Design**
The single endpoint service has been implement using PHP Slim 4.0, PostgreSQL and Redis. Initially the data integration takes place as described on previous paragraph.
All the requested are been parsed by two middlewares.
The first one **ContentTypeMiddleware**, is responsible to check the content-type of the incoming requests. Only three types of input content aare allowed ('application/json', 'application/vnd.api+json', 'application/ld+json'). In all the other cases api responses with 415 status code (unsupported media type).
The second one **LogMiddleware**, is executed after the successful execution of the first one (ContentTypeMiddleware).
On LogMiddleware on every api call a key is added to a timestamp to redis in a queue (FILO) in a personalised key based on the client ip address. 
For the purpose of this assignment, it was chosen to use redis to log all the allowed incoming requests and use as many as technologies the job position requires (instead of using PostgreSQL)
if the limit of the incoming requests has been passed the API returns status code 429 (Too Many Requests!).

For the purpose of this assignment, it was chosen to use a third-party application to log all the allowed incoming requests and use as many as technologies the job position requires (instead of using again redis or PostgreSQL)
The ip address, the Uri and the input of the incoming requests are been logged (timestamp appeared automatically by papertrial).


**Input Tests **
[no input - or null/different fields]  - ContentType NOT Allowed! [415]
{"filters":{"mmsi":[247039300], "points":[{"lon":14.36933, "lat":40.82278},{"lon":18.87793, "lat":42.75178}], "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"application/json"} - brings results
{"filters":{"mmsi":[247039300, 311040700], "points":[{"lon":14.36933, "lat":40.82278},{"lon":18.87793, "lat":42.75178}], "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"application/json"} - brings results
{"filters":{"mmsi":[247039300], "points":[{"lon":14.36933, "lat":42.75178},{"lon":18.87793, "lat":40.82278}], "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"text/csv"} - brings results
{"filters":{"mmsi":[247039300],  "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"text/csv"} - brings results
{"filters":{"mmsi":[247039300], "points":[{"lon":14.36933, "lat":42.75178},{"lon":18.87793, "lat":40.82278}], "ContentType":"text/csv"} - Invalid Input! [400]
{"filters":{"mmsi":[], "points":[{"lon":14.36933, "lat":40.82278},{"lon":18.87793, "lat":42.75178}], "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"application/json"} - - brings results
{"filters":{"mmsi":[], "points":[{"lon":14.36933, "lat":40.82278},{"lon":18.87793, "lat":42.75178}]}, "ContentType":"application/json"} - brings results
{"filters":{"mmsi":[], "points":[{"lon":14.36933, "lat":40.82278},{"lon":18.87793, "lat":42.75178}], "time":{"ts_since":1372683960, "ts_to":1372700340}}} - brings results
{"filters":{"mmsi":[247039300, 311040700], "points":[{"lon":14.36933, "lat":40.82278},{"lon":18.87793, "lat":42.75178}], "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"application/json"} - brings results
{"filters":{"mmsi":[247039300], "points":[{"lon":14.36933, "lat":40.82278},{"lon":18.87793, "lat":42.75178}], "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"application/json"} - Too Many Requests! [429] - wait a minute and try again it will bring results
{"filters":{"mmsi":[247039300], "points":[{"lon":14.36933, "lat":42.75178},{"lon":18.87793, "lat":40.82278}], "time":{"ts_since":1372683960, "ts_to":1372700340}}, "ContentType":"application/json"} - no results / points are not shaping a quadrilateral 


The above tests have been included on a Postman collection on the <root folder>/postman of the project alongside with the endpoint of the API.