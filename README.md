# Billetera virtual en PHP con Laravel

Este proyecto es una billetera virtual desarrollada utilizando el framework Laravel en PHP. La aplicación contendrá las funciones bancarias básicas como: transacciones, asociar tarjetas físicas y virtuales almacenar dinero en un entorno digital y realizar pagos online.

## Desarrolladores

- Jorge Martín Lorente
- Javier Cavalero
- Fabián Loza
- Emiliano Rodriguez

## Requisitos Previos

Asegurate de tener las siguientes versiones de software instaladas antes de comenzar:

- [PHP](https://www.php.net/): Se recomienda PHP 7.3 o superior.
- [Composer](https://getcomposer.org/): Utiliza Composer 2.0 o superior.
- [Git](https://git-scm.com/): Se recomienda Git 2.0 o superior.

## Librerías utilizadas

- [Passport](https://laravel.com/docs/10.x/passport): Para la autenticación.
- [Telescope](https://laravel.com/docs/10.x/telescope): Para el registro de solicitudes.

## Documentación de las solicitudes

Podés encontrar la colección de Postman para este proyecto [acá](https://app.getpostman.com/join-team?invite_code=e2df96e1d220182ac93c4ffdc6a1e702&target_code=ee0bc31aecfcdd6caac6c2742c8ef721) 


## Configuración del entorno local (opcional)

Podés utilizar XAMPP para ejecutar el proyecto de forma local:

1) Descarga e instala [XAMPP](https://www.apachefriends.org/es/download.html)

2) Inicia Apache y MySQL en el panel de control de XAMPP.

3) Ubicate dentro del directorio ``\xampp\htdocs``

## Instalacion del proyecto

1) Clona el repositorio utilizando el siguiente comando dentro de tu terminal

```bash
git clone https://github.com/alkemyTech/MSM-PHP-T1/tree/main
```
2) Cambia al directorio del proyecto

```bash
cd MSM-PHP-T1
```
3) Instala las dependencias del proyecto utilizando Composer

 ```bash
 composer install
 ```
 
4) Crea un archivo .env a partir del archivo de ejemplo ".env.example"

 ```bash
 copy .env.example .env
 ```

5) Genera una clave de aplicación única para Laravel

 ```bash
 php artisan key:generate
 ```

6) Crea la base de datos ejecutando las migraciones

 ```bash
 php artisan migrate
 ```

7) Genera las claves de cifrado para la creación tokens de acceso

 ```bash
 php artisan passport:install
 ```

8) Inicia el servidor

 ```bash
 php artisan serve
 ```

## Respuestas HTTP

- Utilizaremos la convención estándar de respuestas HTTP.

