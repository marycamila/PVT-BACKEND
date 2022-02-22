# PLATAFORMA VIRTUAL DE TRÁMITES

## Requerimientos

- Docker version 20.10.12
- PostgreSQL 12
- PHP version 8.1
- composer

## Configuración e instalación

- Clonar el proyecto *PVT-BACKEND*

```bash
git clone https://github.com/MUTUAL-DE-SERVICIOS-AL-POLICIA/PVT-BACKEND
cd PVT-BACKEND
```

- Configurar el archivo ***docker-compose.yml***. Especificamente las siguientes lineas:

```docker
-   context:./vendor/laravel/sail/runtimes/8.0
+   context:./vendor/laravel/sail/runtimes/8.1

-   image: sail-8.0/app
+   image: sail-8.1/app
```

- Configurar el archivo ***composer.json***. La linea:

```txt
-   "php": "^7.3|^8.0",
+   "php": "^7.3|^8.1",
```

- Instalar dependencias del proyecto con *Composer*
    Instalando dependencias del proyecto, navegando al directorio de la aplicación y ejecutando el siguiente comando. Dicho comando usa un pequeño contenedor Docker que contiene PHP y Composer para instalar las dependencias necesarias de la aplicación.

```docker
docker run --rm \
-u "$(id -u)":$(id -g)" \
-v $(pwd):/var/www/html \
-w /var/www/html \
laravelsail/php80-composer:latest \
composer install --ignore-plataform-reqs
```

- Edite el archivo *`.env`* con las credenciales de la base de datos y variables de entorno.
    - Si necesita cambiar el puerto, agregue en el archivo *`.env`* el puerto que necesite. *Por ejemplo:*
        `APP_PORT=8080`
    - Configure la ip de la base de datos. *Por ejemplo:*
        `DB_HOST=192.168.2.68`
    - Configure el puerto de la base de datos. *Por ejemplo:*
        `DB_PORT=5432`

## Levantar los contenedores en Docker

Para poder empezar a levantar el proyecto ***PVT-BACKEND***, debemos ejecutar el siguiente comando, ubicados primeramente en la carpeta del proyecto.

`./vendor/bin/sail up`

Verificamos si se levantaron los contenedores:

`docker ps -a`

### Ejecutamos el siguiente comando:

`composer install`

Para verificar los cambios realizados en los archivos ***docker-compose.yml*** y ***composer.json***

Entramos al bash de linux, (en nuestro caso)

`docker exec -it <id-contenedor-sail> /bin/bash`

Actualizamos dependencias

`composer install`

Y verificamos el php del contenedor

`php --version`