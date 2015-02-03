<?php

/**
 * Converts a UNIX timestamp to a human-readable date.
 *
 * @param integer $timestamp
 *
 * @return string
 */
function _fswpt_timestamp_to_nice_date($timestamp)
{
    return strftime('%e %h %Y', $timestamp);
}
