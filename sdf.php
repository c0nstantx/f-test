<?php
/**
 * Special Day Fix (SDF)
 */

/** Include autoloader */
include_once 'lib/autoloader.php';

/** Help text */
$helpText =
<<<EOD
    Special days fix

    This script creates a temporary table with normal schedules and assign special
    day as normal schedule and restore.

    --date                  Date. Define the day to process (See here for supported formats: http://php.net/manual/en/datetime.formats.php)
    --vendor[optional]      Vendor ID. Apply the range for a specific vendor.
    --restore               Restore that day
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

/** Init DayFixer*/
$db = $app->get('db');
$dayFixer = new \Foodora\Model\DayFixer($db);

/** Get day to switch */
$inputParser = $app->get('input_parser');
$date = new \DateTime($inputParser->getArgument('date'));

/** Check if specific vendor was defined */
if ($inputParser->hasArgument('vendor')) {
    $vendorId = $inputParser->getArgument('vendor');
} else {
    $vendorId = null;
}

/** Fix or restore a day */
if ($inputParser->hasArgument('restore')) {
    $dayFixer->restoreDay($date, $vendorId);
} else {
    $dayFixer->fixDay($date, $vendorId);
}

return 0;