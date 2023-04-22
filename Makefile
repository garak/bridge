GET_CS = wget https://cs.symfony.com/download/php-cs-fixer-v3.phar -O
CS = vendor/bin/php-cs-fixer
GET_PS = wget https://github.com/phpstan/phpstan/releases/download/1.10.13/phpstan.phar -O
PS = vendor/bin/phpstan

%:
	@:

args = `arg="$(filter-out $@,$(MAKECMDGOALS))" && echo $${arg:-${1}}`

.PHONY: build clear cs install stan start stop test update

help:
	@awk 'BEGIN {FS = ":.*##"; printf "Use: make \033[36m<target>\033[0m\n"} /^[a-zA-Z_-]+:.*?##/ { printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2 } /^##@/ { printf "\n\033[1m%s\033[0m\n", substr($$0, 5) } ' $(MAKEFILE_LIST)

build:	## build image
	docker-compose build

clear:	## clear docker image
	docker rmi -f garak/card

cs:	## coding standard check via php-cs-fixer
	docker-compose exec php /bin/sh -c '{ test -f ${CS} && php ${CS} fix -v; } || { ${GET_CS} ${CS} && php ${CS} fix -v; }'

install:	## install vendors
	docker-compose exec php composer install

stan:	## static analysis via phpstan
	docker-compose exec php /bin/sh -c '{ test -f ${PS} && php ${PS} analyse -v; } || { ${GET_PS} ${PS} && php ${PS} analyse -v; }'

start:	## start docker image
	docker-compose up -d

stop:	## stop docker image
	docker-compose stop

test:	## run test via phpunit
	docker-compose exec php vendor/bin/phpunit

update:	## install vendors
	docker-compose exec php composer update