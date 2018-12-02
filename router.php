<?php

require 'vendor/autoload.php';
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

include_once "entities/User.php";
include_once "entities/UsersList.php";
include_once "entities/Market.php";
include_once "entities/Customer.php";
include_once "entities/Executor.php";
include_once "entities/Task.php";

include_once "storage/psql/PostgresStorage.php";

// todo: I don't need all this storage instances for all the requests

$psqlHost           = "host = localhost";
$psqlPort           = "port = 5432";
$psqlCredentials    = "user = market password=123";

$customersPsqlCfg = new PsqlConfig();
$customersPsqlCfg->host         = $psqlHost;
$customersPsqlCfg->port         = $psqlPort;
$customersPsqlCfg->dbname       = "dbname = customers";
$customersPsqlCfg->credentials  = $psqlCredentials;
$customers = new UserPsqlStorage($customersPsqlCfg, "customers", Customer::class);

$executorsPsqlCfg = clone $customersPsqlCfg;
$executorsPsqlCfg->dbname       = "dbname = executors";
$executors = new UserPsqlStorage($executorsPsqlCfg, "executors", Executor::class);

$market = new Market($customers, null, $executors);
$collector = new RouteCollector();

// todo: handle errors, catch exceptions
// todo: do not handle request with broken parameters, e.g.: balance=12.0Hello

// todo: may be it possible to transform this function into closure?
function pagination(): array
{
    $offset = 0;
    $length = 10; // default value // todo: move it into some named constant

    if(isset($_GET['offset']) && !empty($_GET['offset'])) {
        $offset = intval($_GET['offset']);
    }
    if(isset($_GET['length']) && !empty($_GET['length'])) {
        $length = intval($_GET['length']);
    }
    return array('offset' => $offset, 'length' => $length);
}

function balance(): float
{
    if(!isset($_GET['balance']) || empty($_GET['balance'])) {
        header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request");
        die;
    }
    $balance = floatval($_GET['balance']);
    return $balance;
}

$collector->get("customers", function() use ($market) {
    $ret = pagination();
    $offset = $ret['offset'];
    $length = $ret['length'];

    $list = $market->ListCustomers($offset, $length);
    return json_encode($list);
});
$collector->post("customers", function() use ($market) {
    $balance = balance();

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
$collector->put("customers/{cid}", function ($cid) use ($market) {
    $balance = balance();
    $customer = $market->UpdateCustomer($cid, function (?User $customer) use ($balance) {
        // $customer is not used
        $customer = new Customer();
        $customer->balance = $balance;
        return $customer;
    });
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
    $ret = pagination();
    $offset = $ret['offset'];
    $length = $ret['length'];
    $list = $market->ListExecutors($offset, $length);
    return json_encode($list);
});
$collector->post("executors", function() use ($market) {
    $balance = balance();
    $executor = new Executor();
    $executor->balance = $balance;
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
    $ret = pagination();
    $length = $ret['length'];
    $offset = $ret['offset'];
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