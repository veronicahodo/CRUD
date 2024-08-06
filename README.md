# VCRUD

Basic PHP class for managing simple db requests via the CRUD model

## About

This simple script is a flexable tool for accessing mysql-based
databases.

## Usage

`$crud = new Vcrud($username,$password,$dbhost,$dbname)` to create the object and connect to the database as well.

`$crud->create($table,$fields)` inserts into the database. Fields is just an associated array

`$crud->read($table,$conditions,$orOperand,$sortedBy)` reads from the database. Conditions is a nested array of `[$field,$operator,$value]`. OrOperand specifies if we use an OR operand (true) or the AND operand (false, default). SortedBy is a string passed to the ORDER BY portion of the SQL statement

`$crud->update($table,$fields,$conditions,$orOperand)` updates all entries that meet all the conditions specified. All parameters operate as above.

`$crud->delete($table,$conditions,$orOperand)` deletes all entries that meet all the conditions specified. All parameters operate as above.
