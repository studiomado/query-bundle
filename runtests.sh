#!/bin/bash
composer install
php bin/phpunit -c phpunit.xml
