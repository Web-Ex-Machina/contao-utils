Extension "Utilities" for Contao Open Source CMS
========

2.2.0 - 2025-05-19
- Feat : Add a ValidatorUtil Class to extends Contao Validator

2.1.1 - 2025-01-21
- Fix : `WEM\UtilsBundle\Classes\Files::imageToBase64` can take `\Contao\FilesModel|\Contao\File $objFile` argument

2.1.0 - 2024-11-28
- Feat : add hooks in `Model`

2.0.6 - 2024-11-04
- fix for encryption

2.0.5 - 2024-10-28
- fix: `encrypt_b64` & `decrypt_b64` now works if an empty string is given
- fix for exncryption

2.0.1 - 2024-08-05
- fix: handle null input in Encryption class methods
- feat: Add public alias for ScopeMatcher in services config

2.0.0 - 2024-06-24
- code cleaning and PHP8-2 preparation.

1.0.3 - 2024-03-28
- Feat : ability to cancel a [DropZone](https://www.dropzone.dev/) upload

1.0.2 - 2024-03-12
- Fix missing class import
- Feat : add file transfert is complete & path in function return when using file upload with [DropZone](https://www.dropzone.dev/)

1.0.1 - 2023-09-04
- Add a function to format a filesize
- Add functions to manage file upload with [DropZone](https://www.dropzone.dev/)

1.0.0 - 2023-08-14
- Updated `README.md`
- Cleaned dependencies in `composer.json`

0.3.14 - 2023-08-10
- Fix : password generation function misbehaviour

0.3.13 - 2023-06-15
- Fix : use FQDN for Contao's classes to please autocompletion & static code analysis tools

0.3.12 - 2023-06-01
- Fix : use FQDN for Contao's classes to please autocompletion & static code analysis tools

0.3.11 - 2022-06-11
- Feat : allow PHP8

0.3.10 - 2022-05-13
-  Fix : Adjust default order column logic

0.3.9 - 2022-05-12
-  Feat : Default order value for models

0.3.8 - 2021-12-14
- Fix a cast issue

0.3.7 - 2021-11-23
- Add slashes to special characters for default statement

0.3.6 - 2021-09-14
- Various fix

0.3.5 - 2021-02-05
-  Fix a composer key

0.3.4 - 2021-02-04
- validImageTypes is an array, not a string

0.3.3 - 2021-01-21
- Remove useless classes

0.3.2 - 2021-01-21
- Use Haste Model instead of Contao

0.3.1 - 2020-09-14
- Remove an exclude rule

0.3 - 2020-09-14
- Add a function to convert a Contao image into a base64 file
- Improve the function who convert a base64 into a Contao file
- Add a function to generate a Contao token
- Add a function to generate a generic password
- Add a function to generate a generic numeric code
- Fix an issue with generic model class
- Adjust package keywords
- Fix an issue with unnecessary autoloaded folders


0.2 - 2020-06-02
- Add a class to handle Contao commands

0.1 - 2020-05-23
- Add a generic model to handle most of the methods to retrieve items
- Add a function to convert base64 to Contao files