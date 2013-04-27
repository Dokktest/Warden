Warden
======

Simple PHP user authentication

Warden is an uber simple user authentication library for PHP using a MySQL
database. 

The user table is defined in a configuration ini file and Warden takes care of
the rest.


Sample configuration ini
------------------------
```php

[db_config]
;The table name in the database
table = 'users'

;The fields and types for the database fields
fields[] = 'id'
types[] = 'int(11) not null auto_increment'

fields[] = 'email'
types[] = 'varchar(255)'

fields[] = 'pass'
types[] = 'varchar(255)'

fields[] = 'salt'
types[] = 'varchar(255)'

fields[] = 'score'
types[] = 'int(11)'

fields[] = 'nerd_type'
types[] = 'int(11)'

fields[] = 'nickname'
types[] = 'varchar(255)'

;The indexes for the table.

;Primary key
primary_key = 'id'

;Secondary keys
indexes[] = 'email'

;What fields Warden should use for authentication
[credentials]
identity = 'email'
credential = 'pass'
```
