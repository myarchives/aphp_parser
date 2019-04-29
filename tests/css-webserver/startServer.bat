%~d0
cd %~dp0
@echo off
REM phpunit must be installed. See more : https://phpunit.de/getting-started/phpunit-5.html
REM bat file can run via CMD, open WIN+R CMD and RUN this file "startTest.bat"
@echo on
php -S localhost:8009 -t . routing.php