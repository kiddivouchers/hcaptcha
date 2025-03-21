.PHONY: ci
ci: cs test phpstan

.PHONY: cs
cs: vendor
	composer validate
	vendor/bin/php-cs-fixer fix

.PHONY: test
test: vendor
	vendor/bin/phpunit

.PHONY: phpstan
phpstan: vendor
	vendor/bin/phpstan

vendor: composer.json vendor-bin/*/composer.json vendor-bin/*/composer.lock
	composer update
	composer bin all install
	touch vendor
