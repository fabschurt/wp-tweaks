<?php

require_once __DIR__.'/../../vendor/autoload.php';

$testsDir = '/tmp/wordpress-tests-lib';

require_once "{$testsDir}/includes/functions.php";

tests_add_filter('plugins_loaded', function() {
    require_once __DIR__.'/../../wp-tweaks.php';
});
tests_add_filter('muplugins_loaded', function() {
    // Require main mu-plugin files here
});

require_once "{$testsDir}/includes/bootstrap.php";
