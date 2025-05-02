#!/bin/bash
cd $( dirname -- "$0"; )
git pull
composer install
bin/console doctrine:migrations:migrate
rm -rf var/cache/*