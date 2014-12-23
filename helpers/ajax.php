<?php

/**
 * Cleanly terminates an AJAX call in case of error.
 *
 * @param string $error_msg
 *
 * @return void
 */
function _fswpt_terminate_ajax($error_msg = 'An AJAX error has occured.')
{
    header('HTTP/1.0 500 Internal Server Error');
    header('Content-Type: text/plain; charset=UTF-8');
    echo $error_msg;
    exit();
}
