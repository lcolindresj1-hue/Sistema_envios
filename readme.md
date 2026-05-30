Nombre: LUis Colindres 30/05/2026
09001911896
Sistema de Gestión de Envíos

Requisitos
- PHP 8
- Composer
- XAMPP o Apache
- MySQL

Instalación

1. Clonar repositorio

git clone https://github.com/lcolindresj1-hue/Sistema_envios.git

2. Entrar al proyecto


3. Instalar dependencias

composer install

Configuración

La conexión a la base de datos se configura en:

config/conexion.php

La base de datos principal se encuentra en Aiven.

La base de datos existente también puede instalarse en un servidor MySQL local utilizando el archivo:

database/database_integration.sql

El archivo database/database_integration.sql también se utiliza para pruebas CI/CD.

Ejecución

Con XAMPP:
- Copiar el proyecto en htdocs
- Iniciar Apache y MySQL
- Importar database/database.sql en MySQL o phpMyAdmin
- Abrir:

http://localhost/Sistema_envios

Con servidor PHP:

php -S localhost:8000

Abrir:

http://localhost:8000
