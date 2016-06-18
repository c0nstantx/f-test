<?php
/**
 * Special Day Switcher (SDS)
 */

/** Include autoloader */
include_once 'lib/autoloader.php';

/** @var array $dbOptions  Database connection options */
$dbOptions = require_once __DIR__.'/config/db.php';

/** @var \Foodora\DB\DBInterface $db  Database object */
try {
    $db = \Foodora\DB\DBFactory::getDB($dbOptions);
} catch (\Exception $ex) {
    echo "Connection to database failed: {$ex->getMessage()}\n";

    return -1;
}

/** @var \Foodora\DaySwitcher $daySwitcher */
$daySwitcher = new \Foodora\DaySwitcher($db);

/** Get day to switch */
$inputParser = new \Foodora\InputParser();
$inputParser->parse($argv);

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