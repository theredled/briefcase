#!/bin/bash
cd $( dirname -- "$0"; )
git pull
bin/console doctrine:migrations:migrate
rm -rf var/cache/*