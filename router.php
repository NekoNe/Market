<?php

require 'vendor/autoload.php';
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

set_error_handler(function ($exception){
    error_log("unhandled exception {$exception}");
    header("{$_SERVER['SERVER_PROTOCOL']} 500 Internal Server Error");
});

header('Content-Type: application/json');

include_once "entities/User.php";
include_once "entities/UsersList.php";
include_once "entities/Market.php";
include_once "entities/Customer.php";
include_once "entities/Executor.php";
include_once "entities/Task.php";

include_once "storage/psql/Config.php";
include_once "storage/psql/UsersStorage.php";
include_once "storage/psql/TasksStorage.php";

include_once "exception/ObjectNotFoundException.php";
include_once "exception/DatabaseException.php";
include_once "exception/MarketRuntimeException.php";
include_once "exception/InvalidInputException.php";
include_once "exception/LowBalanceException.php";

// todo: I don't need all this storage instances for all the requests
$psqlHost           = "host = localhost";
$psqlPort           = "port = 5432";
$psqlCredentials    = "user = market password=123";

$customersPsqlCfg = new PsqlConfig();
$customersPsqlCfg->host         = $psqlHost;
$customersPsqlCfg->port         = $psqlPort;
$customersPsqlCfg->dbname       = "dbname = customers";
$customersPsqlCfg->credentials  = $psqlCredentials;

$executorsPsqlCfg = clone $customersPsqlCfg;
$executorsPsqlCfg->dbname       = "dbname = executors";

$tasksPsqlCfg = clone $customersPsqlCfg;
$tasksPsqlCfg->dbname           = "dbname = tasks";

try {
    $customers = new UserPsqlStorage($customersPsqlCfg, "customers", Customer::class);
    $executors = new UserPsqlStorage($executorsPsqlCfg, "executors", Executor::class);
    $tasks = new TasksPsqlStorage($tasksPsqlCfg, "tasks");
}
catch (Exception $e)
{
    header("{$_SERVER['SERVER_PROTOCOL']} 500 Internal Server Error");
    die;
}

$market = new Market($customers, $tasks, $executors);
$collector = new RouteCollector();

// todo: handle errors, catch exceptions
// todo: add top-level exception handler: set_exception_handler

function errorHandlingDecorator()
{
    $args = func_get_args();
    $func = array_shift($args);

    try
    {
        return call_user_func_array($func, $args);
    }
    catch (ObjectNotFoundException $e)
    {
        header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
    }
    catch (InvalidInputException $e)
    {
        header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request");
    }
    catch (LowBalanceException $e)
    {
        header("{$_SERVER['SERVER_PROTOCOL']} 409 Conflict");
    }
    catch (Exception $e)
    {
        header("{$_SERVER['SERVER_PROTOCOL']} 500 Internal Server Error");
    }
    die;
}

function filterNegativeInt($var)
{
    if((string)(int)$var !== $var)
    {
        throw new InvalidInputException();
    }
    if((int) $var < 0)
    {
        throw new InvalidInputException();
    }
}

function pagination(): array
{
    $offset = 0;
    $length = 10; // default value // todo: move it into some named constant

    if(isset($_GET['offset']) && !empty($_GET['offset'])) {
        $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
        if($offset === false) {
            throw new InvalidInputException();
        }
        if($offset < 0) {
            throw new InvalidInputException();
        }
    } // todo: fix copy-paste
    if(isset($_GET['length']) && !empty($_GET['length'])) {
        $length = filter_input(INPUT_GET, 'length', FILTER_VALIDATE_INT);
        if($length === false) {
            throw new InvalidInputException();
        }
        if($length < 0) {
            throw new InvalidInputException();
        }
    }
    return array('offset' => $offset, 'length' => $length);
}

// todo: do not use float values. Use money class
function currencyValue($fieldName): float
{
    $value = filter_input(INPUT_GET, $fieldName, FILTER_VALIDATE_FLOAT);
    if($value === false || $value === null)
    {
        throw new InvalidInputException();
    }
    if($value < 0.0) {
        error_log("{$value} is less than 0.0");
        throw new InvalidInputException();
    }
    return $value;
}

/*
 * Customer related requests
 */

$collector->get("customers", function() use ($market) {
    return errorHandlingDecorator(function() use ($market) {
        $ret = pagination();
        $offset = $ret['offset'];
        $length = $ret['length'];
        $list = $market->ListCustomers($offset, $length);
        return json_encode($list);
    });
});
$collector->post("customers", function() use ($market) {
    return errorHandlingDecorator(function() use ($market) {
        $balance = currencyValue("balance");
        $customer = new Customer();
        $customer->balance = $balance;
        $market->CreateCustomer($customer);
        header("{$_SERVER['SERVER_PROTOCOL']} 201 Created");
        return json_encode($customer);
    });
});
$collector->get("customers/{cid}", function($cid) use ($market) {
    return errorHandlingDecorator(function() use ($market, $cid) {
        filterNegativeInt($cid);
        $customer = $market->ReadCustomer($cid);
        return json_encode($customer);
    });
});
$collector->put("customers/{cid}", function ($cid) use ($market) {
    return errorHandlingDecorator(function() use ($market, $cid) {
        $balance = currencyValue("balance");
        $customer = $market->UpdateCustomer($cid, function () use ($balance) {
            $customer = new Customer();
            $customer->balance = $balance;
            return $customer;
        });
        return json_encode($customer);
    });
});
$collector->delete("customers/{cid}", function($cid) use ($market) {
    return errorHandlingDecorator(function() use ($market, $cid) {
        filterNegativeInt($cid);
        $market->DeleteCustomer($cid);
        return;
    });
});

/*
 * Executor related requests
 */

$collector->get("executors", function() use ($market) {
    return errorHandlingDecorator(function() use ($market) {
        $ret = pagination();
        $offset = $ret['offset'];
        $length = $ret['length'];
        $list = $market->ListExecutors($offset, $length);
        return json_encode($list);
    });
});
$collector->post("executors", function() use ($market) {
    return errorHandlingDecorator(function() use ($market) {
        $balance = currencyValue("balance");
        $executor = new Executor();
        $executor->balance = $balance;
        $market->CreateExecutor($executor);
        header("{$_SERVER['SERVER_PROTOCOL']} 201 Created");
        return json_encode($executor);
    });
});
$collector->get("executors/{eid}", function($eid) use ($market) {
    return errorHandlingDecorator(function() use ($market, $eid) {
        filterNegativeInt($eid);
        $executor = $market->ReadExecutor($eid);
        return json_encode($executor);
    });
});
$collector->delete("executors/{eid}", function($eid) use ($market) {
    return errorHandlingDecorator(function() use ($market, $eid) {
        filterNegativeInt($eid);
        $market->DeleteExecutor($eid);
        return;
    });
});

/*
 * Task related requests
 */

$collector->post("executors/{eid}/tasks/{tid}", function ($eid, $tid) use ($market) {
    return errorHandlingDecorator(function() use ($market, $eid, $tid) {
        filterNegativeInt($eid);
        filterNegativeInt($tid);
        $market->ExecuteTask($eid, $tid);
        return;
    });
});
$collector->post("customers/{cid}/tasks", function ($cid) use ($market) {
    return errorHandlingDecorator(function() use ($market, $cid) {
        filterNegativeInt($cid);
        $value = currencyValue("value");
        $task = new Task();
        $task->value = $value;
        $market->CreateTask($cid, $task);
        header("{$_SERVER['SERVER_PROTOCOL']} 201 Created");
        return json_encode($task);
    });
});
$collector->get("tasks", function() use ($market) {
    return errorHandlingDecorator(function() use ($market) {
        $ret = pagination();
        $length = $ret['length'];
        $offset = $ret['offset'];
        $list = $market->ListTasks($offset, $length);
        return json_encode($list);
    });
});
$collector->get("tasks/{tid}", function($tid) use ($market) {
    return errorHandlingDecorator(function() use ($market, $tid) {
        filterNegativeInt($tid);
        $task = $market->ReadTask($tid);
        return json_encode($task);
    });
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