Extension "Utilities" for Contao Open Source CMS
======================

Functionalities
-------------------
 * Provide some utilities for develop Contao extensions
 * Class to execute commands
 * Class to generate strings
 * Class ScopeMatcher to check if request is backend/frontend
 * Extends Contao Query Builder
 * Extends Contao String Util
 * Extends Contao Model abstract class
 * Provide a service for encrypt and decrypt data with blowfish
 * Provide a Twig Extension to add Twig features as functions and filters

Twig Filters
-------------------
 * `preg_replace`: call the PHP function `preg_replace` with two string arguments: `pattern` and `replace`

System requirements
-------------------

 * Contao 4.* (Manager Edition)

Installation
------------

Clone the extension from Packagist (Contao 4 - Manager Edition) or directly from Composer

Enable Encryption Service
------------
need to be explicitly enabled in your services.yml like this :
```YML
services:
    WEM\UtilsBundle\Classes\Encryption:
        arguments:
            $secret: '%env(APP_SECRET)%'
            $truncateKey: false
```
If you used the old contao service encryption, and you have some old data encrypted with, set truncateKey at true.

Documentation
-------------

 * [Change log][1]
 * [Git repository][2]
 * [Hooks][4]

License
-------

This extension is licensed under the terms of the Apache License 2.0. The full license text is
available in the main folder.

Getting support
---------------

Visit the [support page][3] to submit an issue or just get in touch :)


Installing from Git
-------------------

You can get the extension with this repository URL : [Github][2]

[1]: CHANGELOG.md
[2]: https://github.com/web-ex-machina/contao-utils
[3]: https://github.com/web-ex-machina/contao-utils/issues
[4]: /docs/HOOKS.md