# Special days switcher

## Requirements

PHP > 5.3.0

## Setup

Configure database connection be editing file:
`config/db.php`

```php
<?php
return array(
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'dbname',
    'username' => 'root',
    'password' => '123456'
);
```

## Run
Run `php sds.ph --help` for command help

```bash
Special days switcher

This script switches normal with special days.
It takes a date range and/or vendor id as arguments.

    --date                  Date. Define the day for switch (See here for supported formats: http://php.net/manual/en/datetime.formats.php)
    --vendor[optional]      Vendor ID. Apply the range for a specific vendor.
    --help                  Display this message.
```