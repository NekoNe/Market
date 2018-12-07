Market
======

Description
-----------

This simple application have three basic entities:

* Customers
* Executors
* Tasks

Customer can place a task into Market and set a price. Executor can execute a task for a given price.
Market gets it's fee.

API
---

+----------------------------+----------+-----------------+-------------------+------------------+----------------------------+
|          Route             |  Method  |    Parameters   |       Reply       |     Errors       |        Desctiption         |
+============================+==========+=================+===================+==================+============================+
| ``/customers``             | GET      |  offset=<int>,  | List of customers | * 200(OK)        | Returns list of            |
|                            |          |  lebgth=<int>   |                   |                  | customers with paging      |
+----------------------------+----------+-----------------+-------------------+------------------+----------------------------+
| ``/customers``             | POST     |  balance=<int>  |  Customer object  | * 201(Created)   | Returns new customer       |
|                            |          |                 |                   |                  | object with one's id       |
+----------------------------+----------+-----------------+-------------------+------------------+----------------------------+
| ``/customers/{id}``        | GET      |                 |  Customer object  | * 200(OK)        | Returns existing customer  |
|                            |          |                 |                   | * 404(Not Found) | by one's id                |
+----------------------------+----------+-----------------+-------------------+------------------+----------------------------+
| ``/customers/{id}``        | PUT      |  balance=<int>  | Cusomer object    | * 200(OK)        | Updates customer balance   |
|                            |          |                 |                   | * 404(Not Found) |                            |
+----------------------------+----------+-----------------+-------------------+------------------+----------------------------+
| ``/customers/{id}``        | DELETE   |                 |                   | * 200(OK)        | Delete customer with all   |
|                            |          |                 |                   | * 404(Not Found) | one's tasks                |
+----------------------------+----------+-----------------+-------------------+------------------+----------------------------+
| ``/customers/{id}/tasks``  | POST     |  value=<int>    | Task object       | * 200(OK)        | Creates new task with a    |
|                            |          |                 |                   | * 404(Not Found) | given value                |
+----------------------------+----------+-----------------+-------------------+------------------+----------------------------+



