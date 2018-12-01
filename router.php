<?php

require 'vendor/autoload.php';
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

include_once "entities/market.php";
include_once "entities/customer.php";
include_once "entities/executor.php";
include_once "entities/task.php";

include_once "storages/PostgresStorage.php";

$market = new PostgresStorage(null, null, null);
$collector = new RouteCollector();

// todo: handle errors, catch exceptions
// todo: do not handle request with broken parameters, e.g.: balance=12.0Hello
// todo: it looks like customers/executors handler violates DRY principle. Need some decorators

$collector->get("customers", function() use ($market) {
    $offset = $length = 0;

    if(isset($_GET['offset']) && !empty($_GET['offset'])) {
        $offset = intval($_GET['offset']);
    }
    if(isset($_GET['length']) && !empty($_GET['length'])) {
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
    if($customer == null) {
        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
        die;
    }
    return json_encode($customer);
});
$collector->delete("customers/{cid}", function($cid) use ($market) {
    $market->DeleteCustomer($cid); // todo: error
    return;
});
$collector->post("customers/{cid}/tasks", function ($cid) use ($market) {
    if(!isset($_GET['value']) || empty($_GET['value'])) {
        header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request");
        die;
    }
    $value = floatval($_GET['value']);

    $task = new Task();
    $task->value = $value;

    $market->CreateTask($cid, $task); // todo: check result

    header("{$_SERVER['SERVER_PROTOCOL']} 201 Created");
    return json_encode($task);
});

$collector->get("executors", function() use ($market) {
    $offset = $length = 0;

    if(isset($_GET['offset']) && !empty($_GET['offset'])) {
        $offset = intval($_GET['offset']);
    }
    if(isset($_GET['length']) && !empty($_GET['length'])) {
        $length = intval($_GET['length']);
    }

    $list = $market->ListExecutors($offset, $length);
    return json_encode($list);
});
$collector->post("executors", function() use ($market) {
    $executor = new Executor();
    $executor->balance = 0.0;

    $market->CreateExecutor($executor);

    header("{$_SERVER['SERVER_PROTOCOL']} 201 Created");
    return json_encode($executor);
});
$collector->get("executors/{eid}", function($eid) use ($market) {
    $executor = $market->ReadExecutor($eid);
    if($executor == null) {
        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
        die;
    }
    return json_encode($executor);
});
$collector->delete("executors/{eid}", function($eid) use ($market) {
    $market->DeleteExecutor($eid); // todo: error
    return;
});
$collector->post("executors/{eid}/tasks/{tid}", function ($eid, $tid) use ($market) {
    $market->ExecuteTask($eid, $tid);
    return;
});

$collector->get("tasks", function() use ($market) {
    $offset = $length = 0;

    if(isset($_GET['offset']) && !empty($_GET['offset'])) {
        $offset = intval($_GET['offset']);
    }
    if(isset($_GET['length']) && !empty($_GET['length'])) {
        $length = intval($_GET['length']);
    }

    $list = $market->ListTasks($offset, $length);
    return json_encode($list);
});
$collector->get("tasks/{tid}", function($tid) use ($market) {
    $task = $market->ReadTask($tid);
    if($task == null) {
        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
        die;
    }
    return json_encode($task);
});

$dispatcher = new Dispatcher($collector->getData());

try {
    $response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'],
        PHP_URL_PATH));
} catch (\Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
    die();
} catch (\Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
    header("{$_SERVER['SERVER_PROTOCOL']} 405 Method Not Allowed");
    die;
}

echo $response;