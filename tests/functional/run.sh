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

# Run
tests_dir='./tests/functional'
${tests_dir}/bootstrap.sh
source .test_vars
casperjs test --includes="${tests_dir}/common.js" "${tests_dir}/suite"
