# MagentoConnectorBundle

A connector bundle for the magento platform

## Launching Behat tests
### Pre-requisites
 - a working pim-community-dev installation with Behat support (see http://docs.akeneo.com/latest/contributing/behat.html for details). Run some Behat tests to check that the whole setup works well (with Firefox and Selenium)
 - a working Magento CE 1.9 configured with the right SOAP user and roles (see above). Right now, the Behat tests assume the following configuration:

```
  Magento URL: http://magento.local/
  Server Rewrite: enabled
  SOAP username: adminsoap
  SOAP API Key: adminsoap
```

### Installation
 - install the Magento Connector V2 in your working pim-community-dev installation (see above)
 - run `bin/magento_behat_links` from your pim-community-dev directory

### Running Magento Connector Behat tests
 - from you pim-community-dev directory, launch the following command:

```
bin/behat -c vendor/akeneo/magento-connector-bundle/Pim/Bundle/MagentoConnectorBundle/behat.yml.dist features/magento/
```
