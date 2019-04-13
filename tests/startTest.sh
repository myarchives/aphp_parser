#!/bin/bash
#
# install phpunit before tests
# https://phpunit.de/getting-started/phpunit-5.html
#
# cd /usr/local/bin
# dir
# sudo wget -O phpunit https://phar.phpunit.de/phpunit-5.phar
# sudo chmod +x phpunit
#
# run webserver before test
# startServer.sh
# css-webserver/startServer.sh

BASEDIR=$(dirname "$0")
cd $BASEDIR
phpunit -c phpunit.xml --bootstrap bootstrap_autoload.php . --debug