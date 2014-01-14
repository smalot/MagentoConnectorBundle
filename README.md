# MagentoConnectorBundle for Akeneo

Welcome on the Akeneo PIM Magento connector bundle.

This repository is issued to develop the Magento Connector for Akeneo PIM.

Warning : this connector is not production ready and is intended for evaluation and development purposes only!

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/akeneo/MagentoConnectorBundle/badges/quality-score.png?s=f2f90f8746e80dc5a1e422156672bd3b0bb6658f)](https://scrutinizer-ci.com/g/akeneo/MagentoConnectorBundle/)

# Requirements

 - php5-xml
 - php5-soap
 - Akeneo PIM beta 4 or above

# Installation instruction

Just run the following composer command :

    php composer.phar require akeneo/magento-connector-bundle:v1.0.0-ALPHA1

Then you just have to add the ConnectorBundle to you `AppKernel.php` :

    $bundles[] = new Pim\Bundle\MagentoConnectorBundle\PimMagentoConnectorBundle();

# Configuration

In order to export products to Magento you need to create a soap user on Magento and give him all rights.

After that you can go to `spread > export profiles` on Akeneo PIM and create your first Magento export job.

*Configuration example* :

![Magento connector configuration example](http://i.imgur.com/thNNxtO.png)

# Demo fixtures

To test the connector with the minimum data requirements you can load the demo fictures. Just add this line to your `parameters.yml`

    installer_data: 'PimMagentoConnectorBundle:demo_magento'

# Notes

A standard Magento's installation require some fields to create a products. In order to be as generic as possible, you need to manage them in Akeneo PIM.

The following Magento's attributes are mandatory and need to be created in Akeneo :

- name
- price
- description
- short_description
- tax_class_id

# Bug and issues

This bundle is still under active development. So you could encounter bugs and instabilities. Feel free to report them on this repository's [issue section](https://github.com/akeneo/MagentoConnectorBundle/issues).
