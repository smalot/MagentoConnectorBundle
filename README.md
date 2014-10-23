# MagentoConnectorBundle for Akeneo

Welcome on the Akeneo PIM Magento connector bundle.

This repository is issued to develop the Magento Connector for Akeneo PIM.

Warning : this connector is not production ready and is intended for evaluation and development purposes only!

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/akeneo/MagentoConnectorBundle/badges/quality-score.png?s=f2f90f8746e80dc5a1e422156672bd3b0bb6658f)](https://scrutinizer-ci.com/g/akeneo/MagentoConnectorBundle/)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/2f3066f2-316f-4ed1-8df0-f48d7a1d7f12/mini.png)](https://insight.sensiolabs.com/projects/2f3066f2-316f-4ed1-8df0-f48d7a1d7f12)
[![Build Status](https://travis-ci.org/akeneo/MagentoConnectorBundle.png?branch=master)](https://travis-ci.org/akeneo/MagentoConnectorBundle)

# Requirements

 - php5-xml
 - php5-soap
 - Akeneo PIM 1.2.x stable

# Installation instructions

Please make sure that your version of PHP has support for SOAP and XML (natively coming with PHP for Debian based distributions).

## Installing the Magento Connector in an Akeneo PIM standard installation

If not already done, install Akeneo PIM (see [this documentation](https://github.com/akeneo/pim-community-standard)).

The PIM installation directory where you will find `app`, `web`, `src`, ... is called thereafter `/my/pim/installation/dir`.

Get composer:

    $ cd /my/pim/installation/dir
    $ curl -sS https://getcomposer.org/installer | php

Install the MagentoConnector with composer:

    $ php composer.phar require akeneo/delta-export-bundle:v1.0.0-BETA2@dev
    $ php composer.phar require akeneo/connector-mapping-bundle:v1.0.0-BETA3@dev
    $ php composer.phar require akeneo/magento-connector-bundle:1.1.*@stable

Enable bundles in the `app/AppKernel.php` file, in the `registerBundles` function just before the `return $bundles` line:

    $bundles[] = new Pim\Bundle\MagentoConnectorBundle\PimMagentoConnectorBundle();
    $bundles[] = new Pim\Bundle\DeltaExportBundle\PimDeltaExportBundle();
    $bundles[] = new Pim\Bundle\ConnectorMappingBundle\PimConnectorMappingBundle();

You can now update your database :

    app/console doctrine:schema:update --force

Don't forget to add guzzle in the composer.json of the pim

    "require": {
        "guzzle/service": ">=3.6.0,<3.8.0"
    },


If you want to manage configurable products, you'll need to add [magento-improve-api](https://github.com/jreinke/magento-improve-api) in your Magento installation.

## Installation the Magento Connector in an Akeneo PIM development environment

The following installation instructions are meant for development on the Magento Connector itself.

To install the Magento connector for development purposes, the best way is to clone it anywhere on your file system and create a symbolic link to your Akeneo installation's src folder.

After that, add bundles to your `AppKernel.php` :

    $bundles[] = new Pim\Bundle\MagentoConnectorBundle\PimMagentoConnectorBundle();
    $bundles[] = new Pim\Bundle\DeltaExportBundle\PimDeltaExportBundle();
    $bundles[] = new Pim\Bundle\ConnectorMappingBundle\PimConnectorMappingBundle();

Don't forget to add guzzle in the composer.json of the pim

    "require": {
        "guzzle/service": ">=3.6.0,<3.8.0"
    },

# Configuration

In order to export products to Magento, a SOAP user with full rights has to be created on Magento.

After that you can go to `spread > export profiles` on Akeneo PIM and create your first Magento export job.

*Configuration example* :

![Magento connector configuration example](http://i.imgur.com/bmWa8DT.png?1)

# Demo fixtures

To test the connector with the minimum data requirements, you can load the demo fixtures. Change the `installer_data` line from the `app/config/parameters.yml` file to:

    installer_data: 'PimMagentoConnectorBundle:demo_magento'

# Notes

## Mandatory attributes

The following Magento's attributes are mandatory for Magento and need to be created or mapped in Akeneo :

- name
- price
- description
- short_description
- tax_class_id

# Bug and issues

This bundle is still under active development. Expect bugs and instabilities. Feel free to report them on this repository's [issue section](https://github.com/akeneo/MagentoConnectorBundle/issues).
