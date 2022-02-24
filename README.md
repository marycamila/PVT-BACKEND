# PLATAFORMA VIRTUAL DE TRÁMITES

## Requerimientos

- Docker engine version 20.10.12 o más reciente.
- Docker compose version 1.29.2 o más reciente.
- PostgreSQL 12
- Git

* * *
***NOTA:*** *Este procedimiento se realizó con el sistema operativo *Linux* en su distribución *Ubuntu 20.04.4 LTS*, pero bien puede funcionar en otras distribuciones, se sugiere consultar la documentación oficial.*

## Instalación de Docker Engine

Si ya tiene instalados versiones anteriores de Docker, Puede eliminarlos con el siguiente comando:

```bash
$ sudo apt-get remove docker docker-engine docker.io containerd runc
```

### Instalando usando el repositorio

Configurando el repositorio Docker, para luego instalar y actualizar Docker desde el repositorio.

**1\. Configurar el repositorio**

```bash
$ sudo apt-get update
$ sudo apt-get install \
ca-certificates \
curl \
gnupg \
lsb-release
```

**2\. Agregue la clave GPG oficial de Docker**

```bash
$ url -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg
```

**3\. Configurar el repositorio**

```bash
$ echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
```

### Instalando Docker engine

```bash
$ sudo apt-get update
$ sudo apt-get install docker-ce docker-ce-cli containerd.io
```

### Verificar la instalación

`$ docker --version`

* * *

## Instalación de Docker Compose

Ejecute este comando para descargar la versión estable de Docker Compose:

```bash
$ sudo curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
```

Aplicar permisos ejecutables al binario:

```bash
$ sudo chmod +x /usr/local/bin/docker-compose
```

Verificamos la instalación:

`$ docker-compose --version`

* * *

## Configuración e instalación del proyecto

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

- Descargar dependencias del proyecto con *Composer*
    Descargando dependencias del proyecto, navegando al directorio de la aplicación y ejecutando el siguiente comando. Dicho comando usa un pequeño contenedor Docker que contiene PHP y Composer para instalar las dependencias necesarias de la aplicación.

```docker
docker run --rm \
  -u "$(id -u)":$(id -g)" \
  -v $(pwd):/var/www/html \
  -w /var/www/html \
   laravelsail/php80-composer:latest \
   composer install --ignore-plataform-reqs
```

- Edite el archivo *`.env`* con las credenciales de la base de datos y variables de entorno.
    - Si necesita cambiar el puerto, agregue en el archivo *`.env`* el puerto que necesite.
        Por ejemplo: *
        
        `APP_PORT=8080`
        
    - Configure la ip de la base de datos.
        *Por ejemplo:*
        
        `DB_HOST=192.168.2.68`
        
    - Configure el puerto de la base de datos.
        *Por ejemplo:*
        
        `DB_PORT=5432`
        

## Levantar los contenedores en Docker

Para poder empezar a levantar el proyecto ***PVT-BACKEND***, debemos ejecutar el siguiente comando, ubicados primeramente en la carpeta del proyecto.

`./vendor/bin/sail up`

Verificamos si se levantaron los contenedores:

`docker ps -a`

### Ejecutamos el siguiente comando:

Para verificar los cambios realizados en los archivos ***docker-compose.yml*** y ***composer.json***

Entramos al bash de la Imagen *Sail* , (en nuestro caso)

`docker exec -it <id-contenedor-sail> /bin/bash`

Actualizamos dependencias

`composer install`

Y verificamos el php del contenedor

`php --version`

## Generar la documentación

Para generar la documentación, utilizamos:

`php artisan l5-swagger:generate`