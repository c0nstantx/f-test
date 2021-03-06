<?php
/**
 * Special Day Switcher (SDS)
 */

/** Include autoloader */
include_once 'lib/autoloader.php';

/** Help text */
$helpText =
<<<EOD
    Special days switcher
    
    This script switches normal with special days. 
    It takes a date range and/or vendor id as arguments.

    --date                  Date. Define the day for switch (See here for supported formats: http://php.net/manual/en/datetime.formats.php)
    --vendor[optional]      Vendor ID. Apply the range for a specific vendor.
    --help                  Display this message.
    \n
EOD;

/** Boot console application */
$dbOptions = require_once __DIR__.'/config/db.php';
$app = new \Foodora\Model\ConsoleApp(
    array(
        'db' => $dbOptions,
        'php_min' => '5.3.0',
        'help_text' => $helpText
    )
);
$app->boot($argv);

/** Init DaySwitcher*/
$db = $app->get('db');
$daySwitcher = new \Foodora\Model\DaySwitcher($db);

/** Get day to switch */
$inputParser = $app->get('input_parser');
$date = new \DateTime($inputParser->getArgument('date'));

/** Check if specific vendor was defined */
if ($inputParser->hasArgument('vendor')) {
    $vendorId = $inputParser->getArgument('vendor');
} else {
    $vendorId = null;
}

/** Switch day */
$daySwitcher->switchDay($date, $vendorId);

return 0;