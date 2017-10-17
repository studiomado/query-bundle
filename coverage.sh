#!/bin/bash
php bin/phpunit -c phpunit.xml --coverage-html=html && open html/index.html
