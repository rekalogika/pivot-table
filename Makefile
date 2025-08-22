PHP=php
COMPOSER=composer
SYMFONY=symfony

-include local.mk

.PHONY: test 
test: clean composer-dump phpstan psalm phpunit

.PHONY: composer-dump
composer-dump:
	$(COMPOSER) dump-autoload

.PHONY: phpstan
phpstan:
	$(PHP) vendor/bin/phpstan analyse

.PHONY: psalm
psalm:
	$(PHP) vendor/bin/psalm

.PHONY: rector
rector:
	$(PHP) vendor/bin/rector process > rector.log
	make php-cs-fixer

.PHONY: php-cs-fixer
php-cs-fixer: tools/php-cs-fixer
	PHP_CS_FIXER_IGNORE_ENV=1 $(PHP) $< fix --config=.php-cs-fixer.dist.php --verbose --allow-risky=yes

.PHONY: tools/php-cs-fixer
tools/php-cs-fixer:
	phive install php-cs-fixer

.PHONY: clean
clean:
	$(PHP) vendor/bin/psalm --clear-cache
	rm tests/output/*.md || true

#
# tests
#

.PHONY: phpunit
phpunit: output-remove
	$(eval c ?=)
	$(PHP) vendor/bin/phpunit $(c)

.PHONY: output-remove
output-remove:
	rm -f tests/output/*.md || true

.PHONY: output-copy
output-copy:
	cp tests/output/*.md tests/expectation/

.PHONY: output-regenerate
output-regenerate:
	make phpunit || true
	make output-copy

#
# compose & test data regeneration
#

.PHONY: compose-up
compose-up:
	cd tests && docker compose up -d --wait

.PHONY: compose-down
compose-down:
	cd tests && docker compose down

.PHONY: compose-restart
compose-restart: compose-down compose-up

.PHONY: regenerate
regenerate: compose-up
	cd tests && docker compose exec --user $$(id -u):$$(id -g) database psql -U app -d app -f /resultset/generate.sql

.PHONY: psql
psql:
	cd tests && docker compose exec --user $$(id -u):$$(id -g) database psql -U app -d app