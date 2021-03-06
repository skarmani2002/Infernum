Infernum – Web Application Engine
=================================

Infernum is a lightweight Web Application Engine written in PHP 5.5.

ATTENTION! Infernum is currently under heavy development and therefore not usable in a production environment. Use it at your own risk!


Installation
------------

1. Make sure that the directory `cache/` is writable.

     Example: `$ chmod 0777 cache/` or `$ chown www-data cache/`

2. Execute `install.sql` in your database.

3. Copy the file `config.php.dist` and rename it to `config.php` and adapt the new file according to your needs.

4. Copy the file `websites/default/settings.yml.dist` and rename it to `settings.yml` and adapt the new file according to your needs.

5. Create your first module. Let's call it `vendor/start` and use it as our frontpage. This can be done in `settings.yml`.

6. Copy the file `websites/default/site.yml.dist` and rename it to `site.yml`. To use more modules you can register them there.


Requirements
------------

* You must have at least PHP version 5.5 installed on your system.


Contributors
------------

Thanks to the contributors:

* Christian Neff (secondtruth)
* Martin Lantzsch (LinuxDoku)
* Sebastian Wagner (nobody, sebix)
