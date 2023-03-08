#### dockers

    docker exec -it yusam-php74 sh -c "cd /var/www/data/yusam/github/yusam-hub/db-ext && composer update"
    docker exec -it yusam-php74 sh -c "cd /var/www/data/yusam/github/yusam-hub/db-ext && php migrate"