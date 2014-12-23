<?php

$tests_dir = getenv('WP_TESTS_DIR');
if (!$tests_dir) {
    $tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $tests_dir.'/includes/functions.php';
tests_add_filter('muplugins_loaded', function() {
    require_once __DIR__.'/../wp-tweaks.php';
});

require_once $tests_dir.'/includes/bootstrap.php';
