#!/bin/bash
cd $( dirname -- "$0"; )
git pull
composer install
npm run build
bin/console doctrine:migrations:migrate
rm -rf var/cache/*