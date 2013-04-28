Warden
======

Simple PHP user authentication

Warden is an uber simple user authentication library for PHP using using PDO
with a MySQL database. 


The user table is defined in a configuration ini file and Warden takes care of
the rest. If APC is available, Warden will check for the existence of the ini
file in the cache and use that.

Warden also checks for the existance of the table defined in the ini. If this
table does not exist, Warden will create it based on the ini structure.


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

Sample useage
-------------

```php
$DatabaseHandle = new PDO('mysql:host=localhost;dbname=randomdb','root', '');
$warden = new Warden\Warden('some_config.ini',$dbHandle);

$user = $warden->findByPrimaryKey($someUserId);
```

Warden returns and/or acts upon an stdClass object that contains all the fields
defininf in the ini.


