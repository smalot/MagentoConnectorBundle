Akeneo to Magento Mapping and Transformations Specifications
============================================================

This document descibes how *Akeneo entitities* are mapped and transformed into *Magento Entities*

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
^^^^^^^^^^^^^^^^^^^^^^

=====================  ========================
Akeneo Attribute type   Magento attribute type
=====================  ========================
Date                         Date
File                         N.A.
Identifier                   N.A.
Image                        Media image
Metric                       see below
Multi select                 Multiple select
Number                       Text field
Price                        Price
Simple Select                Dropdown
Text                         Text field
Text area                    Text Area
Yes/No                       Yes/No
=====================  ========================

Metric attribute transformation
'''''''''''''''''''''''''''''''
 - transformed into Text field
 - format : "VALUE UNIT"
 - UNIT: if defined: channel unit with conversion, else unit of the metric


Attribute scope mapping
^^^^^^^^^^^^^^^^^^^^^^^

Akeneo has only Channel as scope, but attribute content can be translated (localizable attribute).

On Magento, there's no localizable property on attribute, only scopes Global, Website and storeview.
As storeviews are usually used for translation on Magento, we map localizable to scope storeview.
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

In case of localizable only attribute, the value from the attribute needs to be sent to all storeviews
matching the locale in all website.

Akeneo attribute group to Magento attribute group
-------------------------------------------------
In Akeneo, attribute groups are attached to attribute whereas in Magento, attribute group is the attached
to the couple Attribute set and attribute.

Adding attribute to attribute set procedure
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
 - check if the attribute group of the Akeneo attribute already exists in the attribute set
 - if not create the attribute group in this attribute set
 - attach the attribute to the attribute set in this group

Akeneo category to Magento category
-----------------------------------

========================  ===========================
Akeneo category property   Magento category property
========================  ===========================
code                         url-key
title (localizable)          Name
N.A.                         Description
N.A.                         Thumbnail image
N.A.                         Page title
========================  ===========================

Category name
^^^^^^^^^^^^^
In Magento, the category name is scoped to storeview, allowing translation. The translations are provided by
the name in the different langages via the storeview to locale mapping.


Akeneo product to Magento product
---------------------------------

Magento mandatory attributes
^^^^^^^^^^^^^^^^^^^^^^^^^^^^
Some attributes are mandatory in Magento and must be sent with products.


Magento specific
^^^^^^^^^^^^^^^^

Some attributes in Magento don't have their counterparts on Akeneo. Here is how we defined them:

==================  ===========================
Magento attribute    Origin
==================  ===========================
   visibility        defined by configuration
==================  ===========================
