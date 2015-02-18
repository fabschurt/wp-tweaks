#!/usr/bin/env bash

if [ $# -lt 3 ]; then
    echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-127.0.0.1}
WP_VERSION=${5-latest}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=/tmp/wordpress

set -ex

install_wp()
{
    if [ -d $WP_CORE_DIR ]; then
        rm -r $WP_CORE_DIR
    fi
    mkdir -p $WP_CORE_DIR

    if [ $WP_VERSION == 'latest' ]; then
        local ARCHIVE_NAME='latest'
    else
        local ARCHIVE_NAME="wordpress-$WP_VERSION"
    fi

    wget -nv -O /tmp/wordpress.tar.gz http://wordpress.org/${ARCHIVE_NAME}.tar.gz
    tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR

    wget -nv -O $WP_CORE_DIR/wp-content/db.php https://raw.github.com/markoheijnen/wp-mysqli/master/db.php
}

install_test_suite()
{
    # portable in-place argument for both GNU sed and Mac OSX sed
    if [[ $(uname -s) == 'Darwin' ]]; then
        local ioption='-i .bak'
    else
        local ioption='-i'
    fi

    # set up testing suite
    if [ -d $WP_TESTS_DIR ]; then
        rm -r $WP_TESTS_DIR
    fi
    mkdir -p $WP_TESTS_DIR
    cd $WP_TESTS_DIR
    svn co --quiet http://develop.svn.wordpress.org/trunk/tests/phpunit/includes/

    wget -nv -O wp-tests-config.php http://develop.svn.wordpress.org/trunk/wp-tests-config-sample.php
    sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR/':" wp-tests-config.php
    sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" wp-tests-config.php
    sed $ioption "s/yourusernamehere/$DB_USER/" wp-tests-config.php
    sed $ioption "s/yourpasswordhere/$DB_PASS/" wp-tests-config.php
    sed $ioption "s|localhost|${DB_HOST}|" wp-tests-config.php

    wp_config_file_path='../wordpress/wp-config.php'
    touch $wp_config_file_path
    grep --color='never' -E "^(define\(\s+'DB_|\\\$table_prefix\s+=).*" wp-tests-config.php >> $wp_config_file_path
    echo "require_once(ABSPATH . 'wp-settings.php');" >> $wp_config_file_path
}

install_db()
{
    # parse DB_HOST for port or socket references
    local PARTS=(${DB_HOST//\:/ })
    local DB_HOSTNAME=${PARTS[0]};
    local DB_SOCK_OR_PORT=${PARTS[1]};
    local EXTRA=""

    if ! [ -z $DB_HOSTNAME ] ; then
        if [[ "$DB_SOCK_OR_PORT" =~ ^[0-9]+$ ]] ; then
            EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
        elif ! [ -z $DB_SOCK_OR_PORT ] ; then
            EXTRA=" --socket=$DB_SOCK_OR_PORT"
        elif ! [ -z $DB_HOSTNAME ] ; then
            EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
        fi
    fi

    # create database
    mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
}

install_wp
install_test_suite
install_db
