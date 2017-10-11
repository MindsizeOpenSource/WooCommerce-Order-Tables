# wp-custom-order-tables
Managed WooCommerce plugin for Liquid Web.

## Background & Purpose
WooCommerce even with CRUD classes in core, still uses a custom post type for orders. By moving orders to use a custom table in the site database, will improve store orders performance.

## Installation
This plugin uses Composer for the autoloader and loading packages. If you are working with code from the `master` branch in a development environment, the packages need to be installed and the autoloader needs to be generated in order for the plugin to work. After cloning this repository, run `composer install` in the root directory of the plugin.

The packaged version of this plugin, which will eventually be available via the WordPress plugin directory, contains the autoloader and installed packages. This means that end users should not need to worry about running the `composer install` command to get things working. Just grab the latest release and go!
