# User Guide for the Magento Connector Bundle

## Mandatory attributes

The following Magento's attributes are mandatory for Magento and have to be created or mapped in Akeneo:

- name
- price
- description
- short_description
- tax_class_id

You can now create export jobs.

## Exporting structure

First, you need to export Akeneo structure (families, attributes, associations, etc.) to Magento, in order to have the same organisation on both sides.

Go to `Spread > Export profiles` and create a new `magento_full_export` profile.

*Magento full export creation*:

![Magento full export creation](./images/userguide/create-full-export.png)
