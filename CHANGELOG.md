# 1.1.13 (2015-**-**)
## New features
 - Add an option to prevent or allow removal of products with type non managed by Akeneo.
 - Add a log to profile calls to Magento.

# 1.1.12 (2015-01-09)
## Bug fixes
 - When a category is moved in Akeneo but stay in the same parent category, change is now correctly passed on Magento.

# 1.1.11 (2015-01-06)
## New features
 - Add an option to avoid generating category URL_KEY and let Magento handle it.
 - Add an option to set the is_anchor property for all categories.
 - Add an option to force attribute set removal.
 - Add the SOAP URL to SoapFault error to add further diagnosis if necessary
 - Only used configurable attributes are now added to build configurable product.

## Bug fixes
 - option "Do nothing" didn't prevent removal of empty families, it now does.
 - job_execution.summary are now displayed correctly during export and in exports history.

# 1.1.8 (2014-12-01)
## Bug fixes
 - removes sending of url_key when updating product, as it breaks with Magento 1.3.1.0 (see http://www.magentocommerce.com/knowledge-base/entry/ee113-later-release-notes#ee113-11302-seo-uniqueness-rules)

## BC Breaks
 - URL key is no longer sent during product update.

# 1.1.7 (2014-11-29)
## Bug fixes
 - remove base64 image representation from error messages

# 1.1.1 (2014-11-12)
## New feature
 - url_key for products and category is generated now on Akeneo's side,
   to avoid duplicate url_key errors from the SOAP API

## Bug fixes
 - configurable images are now properly sent with their types (small, thumbnail, etc...)
 - required property on attribute conflicts with Configurables and has been removed

## BC Breaks
 - ConfigurableProcessor constructor has now an AttributeManager parameter
 - All Step elements services (writers, processors and readers) that uses the addWarning methods must
   have pim_magento_connector.item.magento_item_step has parent service
 - required property is not sent anymore to Magento, as the data is already checked

# 1.1.0 (2014-10-23)
## New feature
 - Add visibility option for products members of variant group
   for example to avoid displaying simple product only

## BC Breaks
 - ProductNormalizer and ConfigurableNormalizer constructors have now a new visibility parameter

# 1.0.1 (2014-09-30)
## Bug fixes
 - Fix association fixtures #252

# 1.0.0 (2014-09-19)

# 1.0.0-RC10 (2014-09-11)
## Bug fixes
- Fixes on media attribute when updating product

# 1.0.0-RC9 (2014-09-09)
## Bug fixes
- Fix check on Magento 1.9

# 1.0.0-RC8 -
## Bug fixes
- Product cleaner is cleaner
- Version detection fix
- Fix a bug with mappings

## Improvement
- Compatibility with pim-community 1.2.0-RC3
- Compatibility with ConnectorBundleBundle BETA-3
- Stop Compatibility with DeltaExportBundle BETA-2

## BC Breaks
- Stop compatibility with pim-community 1.1
- Stop Compatibility with ConnectorBundleBundle BETA-2
- Stop Compatibility with DeltaExportBundle BETA-1

# 1.0.0-RC7 -
## Features
- Custom entity support

## Bug fixes
- Products not assigned to an exported category are not assigned anymore

## Improvements
- Categories are now exported in the right order

# 1.0.0-RC6 -
## Bug fixes
- Fix bug with configurable product export

# 1.0.0-RC5 -
## Bug fixes
- Fix bug during localizable products export

## Improvements
- Fix some php doc
- Fix errors in README

# 1.0.0-RC4 -
- Attribute can be exported into families (AttributeSets)
- Groups can be added into AttributeSets
- Groups can be deleted
- Attribute can be removed from AttributeSets and groups
- AttributeSets can be deleted
- Add a full export job
- Add Magento v1.9 and v1.14 support

## Improvements
- Compatibility with pim-community 1.1
- Compatibility with magento enterprise edition
- delta export for products
- now use connector mapping bundle
- you can separately inform your magento url and wsdl url in export edit mode
- Added possibility to provide credential in edit mode for http authentication

# 1.0.0-RC3 -

## Features

## Improvements

- Option order on creation

## Bug fixes

- Attribute default value is now well normalized for simple and multi-selects

## BC breaks

# 1.0.0-alpha-2 -

## Features

- Added possibility to create, update and move categories
- Added possibility to export associated products' links
- Added possibility to export grouped products
- Added category assigment for simple and configurable products
- Added possibility to export options (create and remove)
- Products, categories and configurables prune after export
- Added possibility to export attributes
- Mapping system between Akeneo and Magento

## Improvements

- Price mapping validation for configurable products
- Fixtures improvements (configurables, linked products, categories, etc)
- Selects for currencies and locales
- Validation for currencies and locales
- New mappign field for attributes, storeviews and categories

## Bug fixes

- Price mapping fixes (computed price was wrong)

## BC breaks
