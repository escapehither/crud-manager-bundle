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
        );

        // ...
    }

    // ...
}
```

1. Install it:

    ```bash
    $ composer require lakion/sylius-elastic-search-bundle
    ```
2. Install elastic search server:

    ```bash
    $ brew install elasticsearch@2.4
    ```

3. Run elastic search server:

    ```bash
    $ elasticsearch
    ```

4. Add this bundle to `AppKernel.php`:

    ```php
    new \FOS\ElasticaBundle\FOSElasticaBundle(),
    new \Lakion\SyliusElasticSearchBundle\LakionSyliusElasticSearchBundle(),
    ```

5. Create/Setup database:

    ```bash
    $ app/console do:da:cr
    $ app/console do:sch:cr
    $ app/console syl:fix:lo
    ```

6. Populate your elastic search server with command or your custom code:

    ```bash
    $ app/console fos:elastic:pop
    ```

7. Import config file in `app/config/config.yml` for default filter set configuration:

    ```yaml
    imports:
       - { resource: "@LakionSyliusElasticSearchBundle/Resources/config/app/config.yml" }
    ```

8. Import routing files in `app/config/routing.yml`:

    ```yaml
    sylius_search:
        resource: "@LakionSyliusElasticSearchBundle/Resources/config/routing.yml"
    ```

8. Configuration reference:

    ```yaml
    lakion_sylius_elastic_search:
        filter_sets:
            mugs:
                filters:
                    product_options:
                        type: option
                        options:
                            code: mug_type
                    product_price:
                        type: price
    ```
 Add encoder for jwt.
mkdir var/jwt
openssl genrsa -out var/jwt/private.pem -aes256 4096
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem


