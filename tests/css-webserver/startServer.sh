#!/bin/bash
#
# run webserver before test
# startServer.sh

BASEDIR=$(dirname "$0")
cd $BASEDIR
php -S localhost:8009 -t . routing.php
