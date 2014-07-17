# 1.0.0-RC7 -
## Bug fixes
- Products not assigned to an exported category are not assigned anymore

## Improvements

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
