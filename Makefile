.QUERYBUNDLE: runtests
runtests: install
	php bin/phpunit -c phpunit.xml

.QUERYBUNDLE: install
install:
	composer install
