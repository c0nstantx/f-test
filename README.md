# Special days switcher (1st approach)

Switch special days with normal schedules and vice versa.

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

## Example

Switch days from 2015-12-24 to 2015-12-26 for all and 2015-12-25 for vendor with ID = 2
```bash
php sds.php --date 2015-12-24
php sds.php --date 2015-12-25
php sds.php --date 2015-12-26
php sds.php --date 2015-12-27 --vendor 2
```

# Special days fix (2nd approach)

Creates a temp database table with normal schedule and use special day as normal schedule and also reverts the process.

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
Run `php sdf.ph --help` for command help

```bash
Special days fix

This script creates a temporary table with normal schedules and assign special
day as normal schedule and restore.

    --date                  Date. Define the day to process (See here for supported formats: http://php.net/manual/en/datetime.formats.php)
    --vendor[optional]      Vendor ID. Apply the range for a specific vendor.
    --restore               Restore that day
    --help                  Display this message.
```

## Example

Backup days from 2015-12-24 to 2015-12-26 for all and 2015-12-25 for vendor with ID = 2

```bash
php sdf.php --date 2015-12-24
php sdf.php --date 2015-12-25
php sdf.php --date 2015-12-26
php sdf.php --date 2015-12-27 --vendor 2
```

Restore days 2015-12-24 to 2015-12-27 for all

```bash
php sdf.php --date 2015-12-24 --restore
php sdf.php --date 2015-12-25 --restore
php sdf.php --date 2015-12-26 --restpre
php sdf.php --date 2015-12-27 --restore
```
