php bin\console doctrine:database:drop --force
php bin\console cache:clear
php bin\console doctrine:database:create
php bin\console doctrine:schema:create