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
 - Akeneo PIM 1.0.0 or above

# Installation instructions

Please make sure that your version of PHP has support for SOAP and XML (natively coming with PHP for Debian based distributions).

## Installing the Magento Connector in an Akeneo PIM standard installation

If not already done, install Akeneo PIM (see [this documentation](https://github.com/akeneo/pim-community-standard)).

The PIM installation directory where you will find `app`, `web`, `src`, ... is called thereafter `/my/pim/installation/dir`.

Get composer:

    $ cd /my/pim/installation/dir
    $ curl -sS https://getcomposer.org/installer | php

Install the MagentoConnector with composer:

    $ php composer.phar require akeneo/magento-connector-bundle:v1.0.0-RC2

Enable the bundle in the `app/AppKernel.php` file, in the `registerBundles` function just before the `return $bundles` line:

    $bundles[] = new Pim\Bundle\MagentoConnectorBundle\PimMagentoConnectorBundle();

## Installation the Magento Connector in an Akeneo PIM developpement environnement

The following installation instructions are meant for developement on the Magento Connector itself.

To install the magento connector for developpement purposes, the best way is to clone it anywhere on your file system and create a symbolic link to your Akeneo installation's src folder.

After that, add the PimMagentoConnectorBundle to your `AppKernel.php` :

    $bundles[] = new Pim\Bundle\MagentoConnectorBundle\PimMagentoConnectorBundle();

# Configuration

In order to export products to Magento, a SOAP user with full rights has to be created on Magento.

After that you can go to `spread > export profiles` on Akeneo PIM and create your first Magento export job.

*Configuration example* :

![Magento connector configuration example](http://i.imgur.com/thNNxtO.png)

# Demo fixtures

To test the connector with the minimum data requirements, you can load the demo fixtures. Change the `installer_data` line from the `app/config/parameters.yml` file to:

    installer_data: 'PimMagentoConnectorBundle:demo_magento'

# Notes

The following Magento's attributes are mandatory for Mangeot and need to be created in Akeneo :

- name
- price
- description
- short_description
- tax_class_id

# Bug and issues

This bundle is still under active development. Expect bugs and instabilities. Feel free to report them on this repository's [issue section](https://github.com/akeneo/MagentoConnectorBundle/issues).
