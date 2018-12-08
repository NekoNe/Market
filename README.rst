Market
======

Description
-----------

This simple application have three basic entities:

* Customers
* Executors
* Tasks

Customer can place a task into Market and set a price. Executor can execute a task for a given price.
Market gets it's fee. Entities are stored in different databases.

API
---

All responses are json objects or empty objects.

+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
| Route                          | Method   | Parameters      | Reply             | Errors           | Description                |
+================================+==========+=================+===================+==================+============================+
|``/customers``                  | GET      | offset=<int>,   | List of customers | * 200(OK)        | Returns list of            |
|                                |          | length=<int>    |                   |                  | customers with paging      |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/customers``                  | POST     | balance=<int>   | Customer object   | * 201(Created)   | Returns new customer       |
|                                |          |                 |                   |                  | object with one's id       |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/customers/{id}``             | GET      |                 | Customer object   | * 200(OK)        | Returns existing customer  |
|                                |          |                 |                   | * 404(Not Found) | by one's id                |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/customers/{id}``             | PUT      | balance=<int>   | Customer object   | * 200(OK)        | Updates customer balance   |
|                                |          |                 |                   | * 404(Not Found) |                            |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/customers/{id}``             | DELETE   |                 |                   | * 200(OK)        | Delete customer with all   |
|                                |          |                 |                   | * 404(Not Found) | one's tasks                |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/customers/{id}/tasks``       | POST     | value=<int>     | Task object       | * 200(OK)        | Creates new task with a    |
|                                |          |                 |                   | * 404(Not Found) | given value                |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/executors``                  | GET      | offset=<int>,   | List of executors | * 200(OK)        | Returns list of            |
|                                |          | length=<int>    |                   |                  | executors with paging      |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/executors``                  | POST     | balance=<int>   | Executor object   | * 201(Created)   | Returns new executor       |
|                                |          |                 |                   |                  | object with one's id       |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/executors/{id}``             | GET      |                 | Executor object   | * 200(OK)        | Returns existing executor  |
|                                |          |                 |                   | * 404(Not Found) | by one's id                |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/executors/{id}``             | DELETE   |                 |                   | * 200(OK)        | Delete executor            |
|                                |          |                 |                   | * 404(Not Found) |                            |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/executors/{id}/tasks/{id}``  | POST     |                 |                   | * 200(OK)        | Transfers money from       |
|                                |          |                 |                   | * 404(Not Found) | to executor. Executor pays |
|                                |          |                 |                   | * 409(Conflict)  | fee. Task is deleted.      |
|                                |          |                 |                   | Customer balance |                            |
|                                |          |                 |                   | is too low.      |                            |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|``/tasks``                      | GET      | length=<int>,   | List of tasks     | * 200(OK)        | Returns list of            |
|                                |          | offset=<int>    |                   |                  | tasks with paging          |
+--------------------------------+----------+-----------------+-------------------+------------------+----------------------------+

Objects
-------

**Customer**::

  {
    "id": <int>,
    "balance: <int>
  }


**Executor**::

  {
    "id": <int>,
    "balance: <int>
  }
  
  
**Task**::

  {
    "id": <int>,
    "value": <int>,
    "customer": <int>
  }
  

Run
---

> php -S localhost:8000 router.php

Examples
--------

**Create new customer**

.. code-block::

  $ curl -X POST 'localhost:8000/customers?balance=1000'
  {"id":1,"balance":1000}

**List customers**

.. code-block::

  $ curl 'localhost:8000/customers'
  {"users":[{"id":"1","balance":"1000"}]}

**Update customer balance**

.. code-block::

  $ curl -X PUT 'localhost:8000/customers/1?balance=5000'
  {"id":"1","balance":5000}

**Create new executor**

.. code-block::

  $ curl -X POST 'localhost:8000/executors'
  {"id":1,"balance":0}

**Create new task**

.. code-block::

  $ curl -X POST 'localhost:8000/customers/1/tasks?value=10'
  {"id":1,"value":10,"customerId":"1"}

**Execute task**

.. code-block::

  $ curl -X POST 'localhost:8000/executors/1/tasks/1'
  $ curl 'localhost:8000/customers'
  {"users":[{"id":"1","balance":"4990"}]}
  $ curl 'localhost:8000/executors/1'
  {"id":"1","balance":5}

