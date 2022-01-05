<?php

// error_reporting(1);

// database
define('DB_HOST', 'localhost');
define('DB_USER', 'postgres');
define('DB_PASS', 'postgres');
define('DB_NAME', 'apiratelimit');
define('DB_PORT', '5432');

define('RATE_PER_PERIOD', 5);
define('PERIOD_IN_SECOND', 60 * 5);