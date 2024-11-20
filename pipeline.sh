#/opt/plesk/php/7.4/bin/php /usr/lib/plesk-9.0/composer.phar update
#/opt/plesk/php/7.4/bin/php /usr/lib/plesk-9.0/composer.phar dump-autoload
/opt/plesk/php/7.4/bin/php /usr/lib/plesk-9.0/composer.phar install  --no-interaction
/opt/plesk/php/7.4/bin/php bin/console doctrine:migrations:migrate --no-interaction
/opt/plesk/node/12/bin/yarn install
/opt/plesk/node/12/bin/yarn build
