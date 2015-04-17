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

# Initial and final blank lines
echo
trap 'echo' EXIT

# Check that WP-CLI is installed and get its path
wp_cli_path="$(dirname ${0})/../../../vendor/bin/wp"
if [[ ! -f $wp_cli_path ]]; then
  echo 'Missing wp-cli executable; make sure you are in the project root, and that you have run `composer install`.'
  exit 1
fi

# Echo everything to STDOUT and stop execution on first error
set -ex

# Reset the test database
php -r "require_once './vendor/autoload.php'; require_once '/tmp/wordpress-tests-lib/includes/bootstrap.php';"

# Insert test users
$wp_cli_path user create john_locke john.locke@box-factory.void --user_pass=password --role=editor

# Clean exit
exit 0
