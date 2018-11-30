<?php

require 'vendor/autoload.php';
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

include_once "entities/market.php";
include_once "entities/customer.php";
include_once "entities/executor.php";
include_once "entities/task.php";

$market = new MySQLMarket(null, null, null);
$collector = new RouteCollector();

// todo: handle errors, catch exceptions
// todo: do not handle request with broken parameters, e.g.: balance=12.0Hello

$collector->get("customers", function() use ($market) {
    $offset = $length = 0;

    if(isset($_GET['offset']) && !empty($_GET)) {
        $offset = intval($_GET['offset']);
    }
    if(isset($_GET['length']) && !empty($_GET)) {
        $length = intval($_GET['length']);
    }

    $list = $market->ListCustomers($offset, $length);
    return json_encode($list);
});

$collector->post("customers", function() use ($market) {
    if(!isset($_GET['balance']) || empty($_GET['balance'])) {
        header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request");
        die;
    }
    $balance = floatval($_GET['balance']);

    $customer = new Customer();
    $customer->balance = $balance;

    $market->CreateCustomer($customer);

    header("{$_SERVER['SERVER_PROTOCOL']} 201 Created");
    return json_encode($customer);
});

$collector->get("customers/{cid}", function($cid) use ($market) {
    $customer = $market->ReadCustomer($cid);
    if ($customer == null) {
        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
        die;
    }
    return json_encode($customer);
});

$collector->delete("customers/{cid}", function($cid) use ($market) {
    $market->DeleteCustomer($cid); // todo: error
    return;
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

echo $response;