#### yusam-hub/db-ext

    "php": "^7.4|^8.0|^8.1|^8.2"

#### tests
    
    php migrate
    sh phpinit

#### setup

    "repositories": {
        ...
        "yusam-hub/php-compatibility": {
            "type": "git",
            "url": "https://github.com/yusam-hub/php-compatibility.git"
        },
        "yusam-hub/db-ext": {
            "type": "git",
            "url": "https://github.com/yusam-hub/db-ext.git"
        }
        ...
    },
    "require": {
        ...
        "yusam-hub/php-compatibility": "dev-master"
        "yusam-hub/db-ext": "dev-master"
        ...
    }