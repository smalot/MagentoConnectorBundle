#!/bin/bash

#
# Create links necessary to execute Magento Connector related Behat
# on an PIM Community dev context
#

# Catalog fixtures
ln -s ../../../vendor/akeneo/magento-connector-bundle/Pim/Bundle/MagentoConnectorBundle/features/Context/catalog/magento features/Context/catalog/

# Features
ln -s ../vendor/akeneo/magento-connector-bundle/Pim/Bundle/MagentoConnectorBundle/features/magento features/

# DB Dump
ln -s ../../../vendor/akeneo/magento-connector-bundle/Pim/Bundle/MagentoConnectorBundle/Context/fixtures/magento_CE_1_9.sql features/Context/fixtures

# Context classes
ln -s ../../vendor/akeneo/magento-connector-bundle/Pim/Bundle/MagentoConnectorBundle/features/Context/MagentoFeatureContext.php features/Context/
ln -s ../../vendor/akeneo/magento-connector-bundle/Pim/Bundle/MagentoConnectorBundle/features/Context/MagentoContext.php features/Context/


