<?php

/*
Plugin Name: WP Tweaks
Plugin URI: https://github.com/fabschurt/wp-tweaks
Description: Some useful WordPress tweaks.
Author: Fabien Schurter
Author URI: http://fabschurt.net/
Version: dev
License: MIT
License URI: http://opensource.org/licenses/MIT

The MIT License (MIT)

Copyright (c) 2014-2015 Fabien Schurter <dev@fabschurt.net>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

// Load dependencies
if (!function_exists('is_plugin_active')) {
    require_once ABSPATH.'wp-admin/includes/plugin.php';
}

// Include helpers
foreach (glob(__DIR__.'/src/helpers/*.php', GLOB_ERR) as $helper_file) {
    require_once $helper_file;
}

// Include actions and filters
require_once __DIR__.'/src/actions.php';
require_once __DIR__.'/src/filters.php';
