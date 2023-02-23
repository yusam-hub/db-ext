#### yusam-hub/db-ext

    "php": "^7.4|^8.1"

#### tests
    
    php migrate
    sh phpinit

#### setup

    "repositories": {
        ...
        "yusam-hub/db-ext": {
            "type": "git",
            "url": "https://github.com/yusam-hub/db-ext.git"
        }
        ...
    },
    "require": {
        ...
        "yusam-hub/db-ext": "dev-master"
        ...
    }