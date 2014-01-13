# MagentoConnectorBundle

Welcome on the Akeneo PIM Magento connector bundle.

This repository is issued to develop the Magento Connector for Akeneo PIM.

Warning : this connector is still under development and not suitable for production environments.

[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/akeneo/MagentoConnectorBundle/badges/quality-score.png?s=f2f90f8746e80dc5a1e422156672bd3b0bb6658f)](https://scrutinizer-ci.com/g/akeneo/MagentoConnectorBundle/)

# Requirements

 - php5-xml
 - php5-soap
 - Akeneo PIM beta 4 or above

# Installation instruction

For now the best way to install the Magento Connector is to clone it on your file system and create a symbolic link to your Akeneo installation's `src` folder.

Then you just have to add the ConnectorBundle to you `AppKernel.php` :

    $bundles[] = new Pim\Bundle\MagentoConnectorBundle\PimMagentoConnectorBundle();

# Configuration

In order to export products to Magento you need to create a soap user on Magento and give him all rights.

After that you can go to `spread > export profiles` on Akeneo PIM and create your first Magento export job.

*Configuration example* :

![Magento connector configuration example](http://i.imgur.com/thNNxtO.png)

# Notes

A standard Magento's installation require some fields to create a products. In order to be as generic as possible, you need to manage them in Akeneo PIM.

The following Magento's attributes ar mandatory and need to be created in Akeneo :

- name
- price
- description
- short_description
- tax_class_id

# Bug and issues

This bundle is still under active development. So you could encounter bugs and instabilities. Feel free to report them on this repository's [issue section](https://github.com/akeneo/MagentoConnectorBundle/issues).
