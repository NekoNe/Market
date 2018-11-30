<?php

require 'vendor/autoload.php';
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;


$collector = new RouteCollector();

// CUSTOMER methods
$collector->get("customers", function(){
    return 'get a list of customers';
});
$collector->post("customers", function(){
    return 'create a customer';
});
$collector->get("customers/{cid}", function($cid){
    return 'get customer '.$cid.' info';
});
$collector->delete("customers/{cid}", function($cid){
    return 'delete customer '.$cid;
});
$collector->post("customers/{cid}/tasks", function ($cid){
    return 'create a task from customer '.$cid;
});

// EXECUTOR methods
$collector->get("executors", function() {
    return 'get a list of executors';
});
$collector->post("executors", function() {
    return 'create an executor';
});
$collector->get("executors/{eid}", function() {
    return 'get an executor info';
});
$collector->delete("executors/{eid}", function() {
    return 'delete an executor';
});
$collector->post("executors/{eid}/tasks/{tid}", function ($eid, $tid){
    return 'executor "'.$eid.'" takes task "'.$tid.'"';
});

// TASK methods
$collector->get("tasks", function() {
    return 'get tasks list';
});
$collector->get("tasks/{tid}", function($tid) {
    return 'get task '.$tid.' info';
});

$dispatcher = new Dispatcher($collector->getData());

try {
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
} catch (\Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
    die();
} catch (\Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
    header("{$_SERVER['SERVER_PROTOCOL']} 405 Method Not Allowed");
    die;
}