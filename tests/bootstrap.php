<?php

require_once __DIR__.'/../vendor/autoload.php';

$testsDir = getenv('WP_TESTS_DIR');
if (!$testsDir) {
    $testsDir = '/tmp/wordpress-tests-lib';
}

require_once "{$testsDir}/includes/functions.php";

tests_add_filter('plugins_loaded', function() {
    // Require main plugin files here
});
tests_add_filter('muplugins_loaded', function() {
    // Require main mu-plugin files here
});

require_once "{$testsDir}/includes/bootstrap.php";
