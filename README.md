# QR Generated API

Proyecto creado como práctica para aprender el uso de autenticación mediante JWT y uso de apis con este token en symfony
7 haciendo uso de los paquetes mínimos para su funcionamiento (Sin utilizar --webapp).

## Paquetes utilizados

### Generales
```
composer require jms/serializer-bundle
composer require friendsofsymfony/rest-bundle
composer require symfony/orm-pack
composer require lexik/jwt-authentication-bundle
composer require symfony/validator
composer require symfony/uid
composer require endroid/qr-code

```

### Develop
```
composer require --dev symfony/maker-bundle
composer require --dev symfony/phpunit-bridge

```

## Configuración del Paquete JWT

Para iniciar el proceso de configuración del paquete JWT en Symfony, es necesario crear las claves pública y privada.
Utilice el siguiente comando para generar las claves SSL:

```
php bin/console lexik:jwt:generate-keypair
```

En caso de que ocurra un error al ejecutar el comando anterior, puede utilizar los comandos alternativos que se detallan
a continuación. Estos comandos le solicitarán una contraseña, la cual debe coincidir con el valor de la variable
JWT_PASSPHRASE definida en el archivo **.env**.

1. Crear el directorio para almacenar las claves:

```
mkdir config/jwt
```
2. Generar la clave privada con cifrado AES-256:

```
openssl genrsa -out config/jwt/private.pem -aes256 4096
```
3. Extraer la clave pública a partir de la clave privada:

```
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```
