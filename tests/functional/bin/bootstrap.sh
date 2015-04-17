#!/usr/bin/env bash

#
# This file is part of the fabschurt/wp-tweaks package.
#
# (c) 2014-2015 Fabien Schurter <dev@fabschurt.net>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.
#
# @author    Fabien Schurter <dev@fabschurt.net>
# @license   MIT
# @copyright 2014-2015 Fabien Schurter
#

# Stop execution on first error
set -e

# Initial and final blank lines
echo
trap 'echo' EXIT

# Check that the working directory is the root of the project
if [[ ! -f './composer.json' ]]; then
  echo 'No `composer.json` file found. You must `cd` into the root of your project before running this script.'
  exit 1
fi

# Check that WP-CLI is installed
wp_cli_path='./vendor/bin/wp'
if [[ ! -f $wp_cli_path ]]; then
  echo 'Missing `wp-cli` executable. Have you run `composer install` yet?'
  exit 1
fi

# Echo everything to STDOUT
set -x

# Reset the test database
php -r "require_once './vendor/autoload.php'; require_once '/tmp/wordpress-tests-lib/includes/bootstrap.php';"

# Insert test data
test_user_id=$($wp_cli_path user create john_locke john.locke@box-factory.void --user_pass=password --role=editor --porcelain)
test_post_id=$($wp_cli_path post create --post_title="Don't tell me what I can't do" --post_content='4 8 15 16 23 42' --post_author=${test_user_id} --post_status='publish' --porcelain)

# Clean exit
exit 0
