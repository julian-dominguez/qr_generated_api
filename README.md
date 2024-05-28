# QR Generated API

Proyecto creado como práctica para aprender el uso de autenticación mediante JWT y uso de apis con este token en symfony
7 haciendo uso de los paquetes mínimos para su funcionamiento (Sin utilizar --webapp).

## Paquetes utilizados

```
composer require jms/serializer-bundle
composer require friendsofsymfony/rest-bundle
composer require --dev symfony/maker-bundle
composer require symfony/orm-pack
composer require lexik/jwt-authentication-bundle
composer require --dev symfony/phpunit-bridge
```

## Configurar el paquete JWT

Primero crearemos las claves pública y privada. Ejecute esto para generar claves SSL:

```
php bin/console lexik:jwt:generate-keypair
```

Si encuentra un error al ejecutar el comando anterior, puede seguir el comando a continuación, el comando le pedirá la
paráfrasis, la paráfrasis debe coincidir con el valor en .env [JWT_PASSPHRASE].


```
mkdir config/jwt
```
```
openssl genrsa -out config/jwt/private.pem -aes256 4096
```
```
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```
