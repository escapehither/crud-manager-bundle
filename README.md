Escape Hither CrudManagerBundle
===============================

Step 1: Download the Bundle
---------------------------
The Bundle is actually in a private Repository.
In your Composer.json add:
```json
{
  //....
  "repositories": [{
    "type": "composer",
    "url": "https://packages.escapehither.com"
  }]

}
```
Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require escapehither/crud-manager-bundle dev-master
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

             new EscapeHither\CrudManagerBundle\StarterKitCrudBundle(),
             new EscapeHither\SecurityManagerBundle\StarterKitSecurityManagerBundle(),
             new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
             new Knp\Bundle\MenuBundle\KnpMenuBundle(),
             new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
             new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),
             new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
        );

        // ...
    }

    // ...
}
```
Step 2: Add the configuration
-----------------------------
1. Import config file in `app/config/config.yml` for default filter set configuration:

    ```yaml
    imports:
       - { resource: "@EscapeHitherCrudManagerBundle/Resources/config/config.yml" }
    ```


2. Import routing files in `app/config/routing.yml`:

    ```yaml
    escape_hither_crud_manager:
        resource: "@EscapeHitherCrudManagerBundle/Resources/config/routing.yml"
        prefix:   /
    ```

3. Add encoder for jwt.
    ```console
    mkdir var/jwt
    openssl genrsa -out var/jwt/private.pem -aes256 4096
    openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
    ```


