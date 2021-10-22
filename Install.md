# PLATAFORMA VIRTUAL DE TRÁMITES

## Requirements

* (Optional) LDAP server to authenticate users
* PostgreSQL 10.4
* Platform Database based [on this project](https://github.com/MUTUAL-DE-SERVICIOS-AL-POLICIA/PVT)
* Composer

## Install

* Clone the project

```sh
    git clone https://github.com/MUTUAL-DE-SERVICIOS-AL-POLICIA/PVT-BACKEND.git
    cd PVT-BACKEND
```

* Install Composer Dependences

You may install the application's dependencies by navigating to the application's directory and executing the following command. This command uses a small Docker container containing PHP and Composer to install the application's dependencies:

```sh
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php80-composer:latest \
    composer install --ignore-platform-reqs
```
    

* Install laravel lenguage Spanish

```sh
    composer require laraveles/spanish
    
    php artisan vendor:publish --tag=lang
    or
    php artisan laraveles:install-lang
```

* Install swagger documentation

```sh
composer require "darkaonline/l5-swagger"

copy in app/Providers/AppServiceProvider.php
    public function register()
    {
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
    }
```

* Edit `.env` file with database credentials and established manteinance modes
* If need change port add in .env file 

```sh
    APP_PORT=8080
```

* Generate keys and compile JS files

```sh
    ./vendor/bin/sail up
```

* To generate the documentation

```sh
    php artisan l5-swagger:generate
```

* To view the documentation unput in your web browser URL: [http://server:port/api/documentation](http://localhost/api/documentation/)
