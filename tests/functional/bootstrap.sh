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

# Install a fresh version of the plugin in the test installation
archive_path='/tmp/wordpress/wp-tweaks.zip'
git archive --format=zip -0 > $archive_path HEAD
if [[ -z $($wp_cli_path plugin is-installed wp-tweaks) ]]; then
  $wp_cli_path plugin uninstall wp-tweaks
fi
$wp_cli_path plugin install $archive_path --activate

# Reset the test database
php -r "require_once './vendor/autoload.php'; require_once '/tmp/wordpress-tests-lib/includes/bootstrap.php';"

# Set the test theme
$wp_cli_path theme activate twentythirteen

# Insert test data and store results in env variables
export _casper_desmond_user_id=$($wp_cli_path user create desmond_hume desmond.hume@the-hatch.void --user_pass=password --role=editor --porcelain)
export _casper_locke_user_id=$($wp_cli_path user create john_locke john.locke@box-factory.void --user_pass=password --role=editor --porcelain)
export _casper_post_1_id=$($wp_cli_path post create --post_title='See you in another life brother' --post_content='Are you him?' --post_author=${_casper_desmond_user_id} --post_status='publish' --porcelain)
export _casper_post_2_id=$($wp_cli_path post create --post_title="Don't tell me what I can't do" --post_content='4 8 15 16 23 42' --post_author=${_casper_locke_user_id} --post_status='publish' --porcelain)
export _casper_post_3_id=$($wp_cli_path post create --post_title='I was wrong' --post_content='108 00' --post_author=${_casper_locke_user_id} --post_status='publish' --porcelain)
export _casper_post_1_url=$($wp_cli_path post url ${_casper_post_1_id})
export _casper_post_2_url=$($wp_cli_path post url ${_casper_post_2_id})
export _casper_post_3_url=$($wp_cli_path post url ${_casper_post_3_id})
