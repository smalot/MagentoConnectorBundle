Akeneo to API Import format and specifications
##############################################

Entities mapping overview
-------------------------

=================  ======================
 Akeneo Entity      Magento Entity
=================  ======================
attribute           attribute
family              attribute set
attribute group     attribute group
category            category
product             simple product
product group       grouped product
variant group       configurable product
associations        linked products
=================  ======================


Akeneo Attribute to Magento attribute
-------------------------------------
Please note that no validation rule (number min, max characters, validation regexp, etc...) are sent to Magento, as the
content of the attributes is already checked on Akeneo's side.

=======================  ====================
Akeneo property           Magento property
=======================  ====================
code                      code
attribute type            attribute type
scope                     scope
localizable               scope
locale specific           N.A.
usable as grid column      ?
usable as grid filter      ?
metric family             see below
created                   N.A.
updated                   N.A.
=======================  ====================

Attribute type mapping
======================
=====================  ====================================
Akeneo Attribute type   Magento attribute type
=====================  ====================================
Date                         Date
File                         N.A.
Identifier                   Text field with unique value
Image                        Media image
Metric                       see below
Multi select                 Multiple select
Number                       Text field
Price                        Price
Simple Select                Dropdown
Text                         Text field
Text area                    Text Area
Yes/No                       Yes/No
=====================  ====================================

Metric attribute transformation
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 - transformed into Text field
 - format : "VALUE UNIT"
 - UNIT: if defined: channel unit with conversion, else unit of the metric

Attribute scope mapping
=======================
Akeneo has only Channel as scope, but attribute content can be translated (localizable attribute).

On Magento, there's no localizable property on attribute, only scopes Global, Website and store view.
As storeviews are usually used for translation on Magento, we map localizable to scope store view.
Storeviews are children of website.

===================   ===========  ====================
           Akeneo side                Magento side
---------------------------------  --------------------
Scopable on channel   Localizable        Scope
===================   ===========  ====================
       -                 -              Global
       X                 -              Website
       -                 X              Storeview (see below)
       X                 X              Storeview
===================   ===========  ====================

In case of localizable only attribute, the value from the attribute needs to be sent to all store views
matching the locale in all website.


Api Import attributes format overview
-------------------------------------

| This is description of attributes used in the Magento CSV and Api Import format. You can retrieve all those attributes
| in Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper. If you need an attribute which is not here, you
| can try to create it in Magento and export it in CSV. CSV format is the same in export and import.

Product Export
==============

General attributes
^^^^^^^^^^^^^^^^^^
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
| Api Import       |                      Description                                                                  |     Value        |Required | Akeneo     |
| attribute label  |                                                                                                   |                  |         | Provider   |
+==================+===================================================================================================+==================+=========+============+
|name              | The name of the product.                                                                          | Text             |   yes   | | Attribute|
|                  |                                                                                                   |                  |         | | mapping  |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
|description       | Description of the product.                                                                       | Text             |   yes   | | Attribute|
|                  |                                                                                                   |                  |         | | mapping  |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
|short_description | Short description of the product                                                                  | Text             |   yes   | | Attribute|
|                  |                                                                                                   |                  |         | | mapping  |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
|sku               | Stock-Keeping Unit. Unique identifier to track your product.                                      | Alpha-           |   yes   | | Attribute|
|                  |                                                                                                   | numeric          |         | | mapping  |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
|_type             | Specifies the type of product. This field indicates whether this product is a simple or           | | - simple       |   yes   | | See      |
|                  | complex product (complex products being those that require additional configuration).             | | - grouped      |         | | entities |
|                  | See below for more information about authorized products types.                                   | | - configurable |         | | mapping  |
|                  |                                                                                                   |                  |         | | above    |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
|_attribute_set    | Refers to the attribute set you want to add your product.                                         | Alpha-           |   yes   | | Attr. set|
|                  |                                                                                                   | numeric          |         | | Mapping  |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
|status            | If set to 1 (enabled) the product will be available for                                           | 1, 2             |   yes   |  Config    |
|                  | sale in your store. If set to 2 (disabled) the product                                            |                  |         |            |
|                  | will not appear in your catalog.                                                                  |                  |         |            |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
|visibility        | Specify whether this product is visible from the catalog, search, both, or neither.               | 1, 2, 3, 4       |   yes   |  Config    |
|                  | See below for more information about authorized products types.                                   |                  |         |            |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+
|weight            | Product weight.                                                                                   | Numeric value    |   yes   | | Attribute|
|                  |                                                                                                   |                  |         | | mapping  |
+------------------+---------------------------------------------------------------------------------------------------+------------------+---------+------------+

Types
`````
+--------------+--------------------------------------------------------------------------------------+
| Types        |       Description                                                                    |
+--------------+--------------------------------------------------------------------------------------+
| simple       | Physical items that are generally sold as single units or in fixed quantities.       |
+--------------+--------------------------------------------------------------------------------------+
| grouped      | A set of products that are related in some way that can logically be sold as a set.  |
+--------------+--------------------------------------------------------------------------------------+
| configurable | A product with variations that the customer has the option to select.                |
+--------------+--------------------------------------------------------------------------------------+
| virtual      | Not supported.                                                                       |
+--------------+--------------------------------------------------------------------------------------+
| giftcard     | Not supported.                                                                       |
+--------------+--------------------------------------------------------------------------------------+
| bundle       | Supported by Api Import but not by Akeneo.                                           |
+--------------+--------------------------------------------------------------------------------------+

Visibility
``````````
+-------+---------------------------------+
| Value |       Description               |
+-------+---------------------------------+
| 1     | Not visible individually.       |
+-------+---------------------------------+
| 2     | Catalog.                        |
+-------+---------------------------------+
| 3     | Search.                         |
+-------+---------------------------------+
| 4     | Catalog, Search.                |
+-------+---------------------------------+



Stores attributes
^^^^^^^^^^^^^^^^^
+------------------+------------------------------------------------------------+----------------+---------+-------------+
| Api Import       |                      Description                           |     Value      |Required | Akeneo      |
| attribute label  |                                                            |                |         | Provider    |
+==================+============================================================+================+=========+=============+
|_product_websites | Refers to the Website code in the Manage Stores section    | Alpha-         |   yes   |  Config     |
|                  | you want to add your products.                             | numeric _      |         |             |
+------------------+------------------------------------------------------------+----------------+---------+-------------+
| _store           | Refers to the store view code in the Manage Stores section | Alpha-         |   yes   | | Store view|
|                  | you want to add your products. Can be blank if store is    | numeric _      |         | | Mapping   |
|                  | the default one.                                           |                |         |             |
+------------------+------------------------------------------------------------+----------------+---------+-------------+


Prices attributes
^^^^^^^^^^^^^^^^^
+------------------+------------------------------------------------------------+----------------+---------+-------------+
| Api Import       |                      Description                           |     Value      |Required | Akeneo      |
| attribute label  |                                                            |                |         | Provider    |
+==================+============================================================+================+=========+=============+
| price            | Product price. Compared to Akeneo, Magento has only one    | Numeric value  |   yes   | | Attribute |
|                  | price with one currency and converts it for others         |                |         | | mapping   |
|                  | currencies. We need to add a "Default currency" field      |                |         |             |
|                  | in Magento configuration screen to know which currency     |                |         |             |
|                  | send to Magento.                                           |                |         |             |
+------------------+------------------------------------------------------------+----------------+---------+-------------+
| tax_class_id     | Specify the tax class ID, which will determine which tax   |    Integer     |   yes   |  Config     |
|                  | rules to apply to the product. Value is an integer based   |                |         |             |
|                  | on id # next to each product tax class.                    |                |         |             |
|                  | (Admin > Sales > Tax > Product Tax Class) The default      |                |         |             |
|                  | value for all exported products is to provide in the       |                |         |             |
|                  | Magento configuration screen.                              |                |         |             |
+------------------+------------------------------------------------------------+----------------+---------+-------------+


Category attributes
^^^^^^^^^^^^^^^^^^^
+------------------+------------------------------------------------------------+----------------+---------+-----------+
| Api Import       |                      Description                           |     Value      |Required | Akeneo    |
| attribute label  |                                                            |                |         | Provider  |
+==================+============================================================+================+=========+===========+
| _category        | Path of the product category from root category (not       |     String     |   no    |  Product  |
|                  | included) to the category separated by /.                  |                |         |  category |
|                  | E.g. : 'Parent Category/My Category'.                      |                |         |           |
+------------------+------------------------------------------------------------+----------------+---------+-----------+
| _root_category   | Name of the '_category' root. Require if _category is fill.|     String     |   no    |  Product  |
|                  |                                                            |                |         |  category |
+------------------+------------------------------------------------------------+----------------+---------+-----------+


Associated products attributes
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
+---------------------+------------------------------------------------------------+-------------+---------+-----------+
|   Api Import        |                      Description                           |    Value    |Required | Akeneo    |
|   attribute label   |                                                            |             |         | Provider  |
+=====================+============================================================+=============+=========+===========+
|_links_upsell_sku    | To be fill with sku of the linked product.                 | Alpha-      |    no   | Product   |
|                     |                                                            | numeric     |         | assoc°    |
+---------------------+------------------------------------------------------------+-------------+---------+-----------+
|_links_crosssell_sku | To be fill with sku of the linked product.                 | Alpha-      |    no   | Product   |
|                     |                                                            | numeric     |         | assoc°    |
+---------------------+------------------------------------------------------------+-------------+---------+-----------+
|_links_related_sku   | To be fill with sku of the linked product.                 | Alpha-      |    no   | Product   |
|                     |                                                            | numeric     |         | assoc°    |
+---------------------+------------------------------------------------------------+-------------+---------+-----------+


Configurable products attributes
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
| Base price is the configurable product price. When you send a configurable product, you need to build a first line
| with information of a product from the configurable (the variant group in Akeneo PIM) and the field '_type' to
| 'configurable'. Then, you can link your previously sent simple products to the configurable.

+----------------------------+-----------------------------------------------------+-------------+---------+-----------+
|   Api Import               |                      Description                    |    Value    |Required | Akeneo    |
|   attribute label          |                                                     |             |         | Provider  |
+============================+=====================================================+=============+=========+===========+
|_super_products_sku         | Sku of the associated product. In Akeneo PIM it's a | Alpha-      |    no   | Variant   |
|                            | product which is in the variant group.              | numeric _   |         | group     |
+----------------------------+-----------------------------------------------------+-------------+---------+-----------+
|_super_attribute_code       | Code of the simple select attribute, the variation  | Alpha-      |    no   | Variant   |
|                            | axis.                                               | numeric _   |         | group     |
+----------------------------+-----------------------------------------------------+-------------+---------+-----------+
|_super_attribute_option     | Code of the simple select option.                   | Alpha-      |    no   | Variant   |
|                            |                                                     | numeric _   |         | group     |
+----------------------------+-----------------------------------------------------+-------------+---------+-----------+
|_super_attribute_price_corr | Difference between base price and associated        | Numeric     |    no   | Variant   |
|                            | product price.                                      | value       |         | group     |
+----------------------------+-----------------------------------------------------+-------------+---------+-----------+


Image products attributes
^^^^^^^^^^^^^^^^^^^^^^^^^
| If an image has to be sent, those four attributes are required, but they're not required to send a product.
| * Code part is variable.

+---------------------------+----------------------------------------------------------+--------+---------+----------+
|   Api Import              |                      Description                         | Value  |Required | Akeneo   |
|   attribute label         |                                                          |        |         | Provider |
+===========================+==========================================================+========+=========+==========+
|(attribute_code)*          | Name of the image file with its extension preceded by /. | String |   yes   |  Media   |
+---------------------------+----------------------------------------------------------+--------+---------+----------+
|(attribute_code)*_content  | Content of the image file in base64 format.              |  Text  |   yes   |  Media   |
+---------------------------+----------------------------------------------------------+--------+---------+----------+
| _media_image              | Name of the image file with its extension preceded by /. | String |   yes   |  Media   |
+---------------------------+----------------------------------------------------------+--------+---------+----------+
| _media_is_disabled        | Media is disabled or not.                                |  0, 1  |   yes   |  Media   |
+---------------------------+----------------------------------------------------------+--------+---------+----------+


Not provided by Akeneo attributes
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
+------------------+------------------------------------------------------------+----------------+---------+
| Api Import       |                      Description                           |     Value      |Required |
| attribute label  |                                                            |                |         |
+==================+============================================================+================+=========+
| created_at       | Date which the product is created. The field is fill by    |  Y-m-d H:i:s   |   no    |
|                  | the product creation date in ProductNormalizer.            |                |         |
+------------------+------------------------------------------------------------+----------------+---------+
| updated_at       | Last product update date. The field is fill by             |  Y-m-d H:i:s   |   no    |
|                  | the product date export in ProductNormalizer.              |                |         |
+------------------+------------------------------------------------------------+----------------+---------+


How to build your data to send products
=======================================

| Sending product have to be as array format. You need arrays to contain your products and then an array to contain all products.
| [ [product_1], [product_2] ]

Lines will be read in Api Import in the same sense you send it. With the previous example product_1 will be read before product_2,
and with [ [product_2], [product_1] ] product_2 will be read before 1. This mechanic allows to update your products directly after
its creation. Putting update information in the line immediately after the creation, you don't have to repeat creation required information.
| [ [product_1 creation], [product_1 update], [product_2 creation], [product_2 update], [product_2 update] ]
Several update lines can follow.

Example
^^^^^^^
Send two simple products with upsell association
````````````````````````````````````````````````
| [
|     [
|         'sku'               => 'sku_1',
|         '_type'             => 'simple',
|         'name'              => 'product ( 1 )',
|         'description'       => 'description',
|         'short_description' => 'short description',
|         '_product_websites' => 'base',
|         '_attribute_set'    => 'Default',
|         '_category'         => 'Parent/My category',
|         '_root_category'    => 'Root category',
|         'color'             => 'red',
|         'status'            => 1,
|         'visibility'        => 4,
|         'tax_class_id'      => 0,
|         'price'             => 659,
|         'weight'            => '785',
|     ],
|     [
|         'sku'               => 'sku_2',
|         '_type'             => 'simple',
|         'name'              => 'product ( 2 )',
|         'description'       => 'description',
|         'short_description' => 'short description',
|         '_product_websites' => 'base',
|         '_attribute_set'    => 'Default',
|         'color'             = 'yellow',
|         'status'            => 1,
|         'visibility'        => 4,
|         'tax_class_id'      => 0,
|         'price'             => 563,
|         'weight'            => '461'
|     ],
|     [
|         '_links_upsell_sku' => 'sku_1'
|     ]
| ]

First, we need to create simple products and then we update sku_2 with association.


Send a simple product with localized attributes
```````````````````````````````````````````````
| [
|     [
|         'sku'               => 'sku_1',
|         '_type'             => 'simple',
|         'name'              => 'product ( 1 )',
|         'description'       => 'description',
|         'short_description' => 'short description',
|         '_product_websites' => 'base',
|         '_attribute_set'    => 'Default',
|         '_category'         => 'Parent/My category',
|         '_root_category'    => 'Root category',
|         'status'            => 1,
|         'visibility'        => 4,
|         'tax_class_id'      => 0,
|         'price'             => 659,
|         'weight'            => '785',
|     ],
|     [
|         '_store'            => 'fr_fr',
|         'description'       => 'Description du produit',
|         'short_description' => 'Une courte description',
|         'name'              => 'Un produit ( 1 )',
|     ],
|     [
|         '_store'            => 'de_de',
|         'description'       => 'Produktbeschreibung',
|         'short_description' => 'kurze Produktbeschreibung',
|         'name'              => 'Produkt ( 1 )',
|     ]
| ]

First, we create the product and then we update it with localized attributes.

Send a configurable product
```````````````````````````

| [
|     [
|         'description'             => 'Description',
|         '_attribute_set'          => 'Default',
|         'short_description'       => 'Short description',
|         '_product_websites'       => 'base',
|         'status'                  => 1,
|         'visibility'              => 4,
|         'tax_class_id'            => 0,
|         'is_in_stock'             => 1,
|         'sku'                     => 'configurable_1',
|         '_type'                   => 'configurable',
|         'name'                    => 'configurable ( 1 )',
|         'price'                   => 385,
|         'weight'                  => 914,
|         '_super_products_sku'     => 'sku_1',
|         '_super_attribute_code'   => 'color',
|         '_super_attribute_option' => 'red',
|     ],
|     [
|         '_super_products_sku'     => 'sku_2',
|         '_super_attribute_code'   => 'color',
|         '_super_attribute_option' => 'yellow',
|     ]
| ]

First, we need to send sku_1 and sku_2 as we seen in the first example. Then, we can create the configurable product and
begin to associate it the sku_1 product on the color variant axis. To finish, we update configurable with sku_2.


Categories Export
=================
Not tested yet.

+------------------+---------------------------------------+-------------+----------+-----------+
| Api Import       |             Description               |    Value    | Required | Akeneo    |
| attribute label  |                                       |             |          | Provider  |
+==================+=======================================+=============+==========+===========+
| name             | Category name.                        |    String   |   yes?   |  category |
+------------------+---------------------------------------+-------------+----------+-----------+
| _category        | Category name. ?                      |    String   |   yes?   |  category |
+------------------+---------------------------------------+-------------+----------+-----------+
| _root            | Category root name.                   |    String   |   yes    |  category |
+------------------+---------------------------------------+-------------+----------+-----------+
| is_active        | Is active or not.                     |    0, 1     |   yes    |   conf ?  |
+------------------+---------------------------------------+-------------+----------+-----------+
| include_in_menu  | Is include in menu or not.            |    0, 1     |   yes    |   conf ?  |
+------------------+---------------------------------------+-------------+----------+-----------+


Attribute sets Export
=====================
* You can add attribute group as many as you want (from zero to many). Each added attribute group is a new line.

+-------------------------+---------------------------------------------+-----------+------------+----------+
|   Api Import            |             Description                     |  Value    |  Required  | Akeneo   |
|   attribute label       |                                             |           |            | Provider |
+=========================+=============================================+===========+============+==========+
| attribute_set_name      | Attribute set name.                         |  String   |    yes     |  Family  |
+-------------------------+---------------------------------------------+-----------+------------+----------+
| sortOrder               | Attribute set sort order.                   |  Numeric  |    no      |   no     |
+-------------------------+---------------------------------------------+-----------+------------+----------+
| (attribute_group_code)* | Attribute group sort order in the attribute |  Numeric  |    no      | Attribute|
|                         | set. You can add an attribute group code in |           |            | group    |
|                         | the label without sort order.               |           |            |          |
+-------------------------+---------------------------------------------+-----------+------------+----------+

Example to build data to send attribute sets
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
| [
|     [
|         'attribute_set_name' => 'set 1',
|         'sortOrder' => 1,
|         'General' => 1,
|         'Prices' => 2,
|         'Marketing' => 3,
|         'Color' => 4,
|         'Size' => 5
|     ]
| ]

Attribute export
================
+-------------------------+------------------------------------------------------+-----------+------------+-----------+
|   Api Import            |             Description                              |  Value    |  Required  | Akeneo    |
|   attribute label       |                                                      |           |            | Provider  |
+=========================+======================================================+===========+============+===========+
| attribute_id            | Attribute code                                       | Alpha-    |    yes     | Attribute |
|                         |                                                      | numeric _ |            |           |
+-------------------------+------------------------------------------------------+-----------+------------+-----------+
| type                    | Attribute type. See below for more information.      | See below |    yes     | Attribute |
+-------------------------+------------------------------------------------------+-----------+------------+-----------+
| default                 | Default value.                                       | Depending |    no      | Attribute |
|                         |                                                      | on type   |            |           |
+-------------------------+------------------------------------------------------+-----------+------------+-----------+
| label                   | Label                                                | String    |    ?       | Attribute |
+-------------------------+------------------------------------------------------+-----------+------------+-----------+
| required                | Is attribute required to send a product ?            | boolean   | ?          | Attribute |
+-------------------------+------------------------------------------------------+-----------+------------+-----------+
| global                  | Store is global                                      | boolean   | ?          | ?         |
+-------------------------+------------------------------------------------------+-----------+------------+-----------+
| visible_on_front        | Is attribute visible on front-end ?                  | boolean   | ?          | No        |
+-------------------------+------------------------------------------------------+-----------+------------+-----------+

Attribute types
^^^^^^^^^^^^^^^
+-------------------+-------------+----------------------------------------------------------------------------------+
|  Attribute types  |   Value     |             Magento description                                                  |
+===================+=============+==================================================================================+
| Text field        | text        | A single line input field for text.                                              |
+-------------------+-------------+----------------------------------------------------------------------------------+
| Text Area         | textarea    | A multiple-line input field for long text.                                       |
+-------------------+-------------+----------------------------------------------------------------------------------+
| Date              | date        | Format ?                                                                         |
+-------------------+-------------+----------------------------------------------------------------------------------+
| Yes/No            | boolean     | Displays a drop-down list with the pre-defined options, “Yes” and “No.”          |
+-------------------+-------------+----------------------------------------------------------------------------------+
| Dropdown          | select      | Displays a drop-down list of values that allows only one selection to be made.   |
|                   |             | The Dropdown input type is used to select options for a configurable product.    |
+-------------------+-------------+----------------------------------------------------------------------------------+
| Multiple select   | multiselect | Displays a drop-down list of values that allows multiple selections to be made.  |
+-------------------+-------------+----------------------------------------------------------------------------------+
| Price             | price       | Price                                                                            |
+-------------------+-------------+----------------------------------------------------------------------------------+
| Fixed Product Tax | weee        | The Fixed Product Tax input field lets you define the FPT rates according to the |
|                   |             | requirements of your locale.                                                     |
+-------------------+-------------+----------------------------------------------------------------------------------+


Associate attribute to attribute group and attribute set
========================================================
In Akeneo, attribute groups are attached to attribute whereas in Magento, attribute group is the attached
to the couple Attribute set and attribute.

+-------------------------+---------------------------------------------+-----------+------------+-----------+
|  Api Import             |             Description                     |  Value    |  Required  | Akeneo    |
|  attribute label        |                                             |           |            | Provider  |
+=========================+=============================================+===========+============+===========+
|  attribute_id           | Attribute code.                             | Alpha-    |    yes     | Attribute |
|                         |                                             | numeric _ |            | group     |
+-------------------------+---------------------------------------------+-----------+------------+-----------+
|  attribute_set_id       | Attribute set code you want to add your     | Alpha-    |    yes     | Family    |
|                         | attribute.                                  | numeric _ |            |           |
+-------------------------+---------------------------------------------+-----------+------------+-----------+
|  attribute_group_id     | Attribute group code you want to add your   | Alpha-    |    yes     | Attribute |
|                         | attribute.                                  | numeric _ |            | group     |
+-------------------------+---------------------------------------------+-----------+------------+-----------+
|  sortOrder              | Attribute sort order in the attribute group | Numeric   |    no      | No        |
|                         |                                             |           |            |           |
+-------------------------+---------------------------------------------+-----------+------------+-----------+


Example to build data to associate attribute to attribute groups and sets
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
| [
|     [
|         'attribute_id' => 'attr_test_2',
|         'attribute_set_id' => 'set 1',
|         'attribute_group_id' => 'Size',
|         'sort_order' => 2,
|     ]
| ]
