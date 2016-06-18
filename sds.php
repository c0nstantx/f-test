<?php
/**
 * Special Day Switcher (SDS)
 */

/** Include autoloader */
include_once 'lib/autoloader.php';

/** Boot console application */
$dbOptions = require_once __DIR__.'/config/db.php';
$app = new \Foodora\ConsoleApp(array('db' => $dbOptions));
$app->boot($argv);

/** Init DaySwitcher*/
$db = $app->get('db');
$daySwitcher = new \Foodora\DaySwitcher($db);

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