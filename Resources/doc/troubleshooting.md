# Cannot create image
This error is in fact pretty rarely linked to images themselves. When the Magento Connector Bundle sends the image after the product has been created or updated, Magento goes through the Product save event flow. On this event, the url_key is generated. If a product has already been created with the same name, the url_key cannot be generated and error is issued, triggering an "Cannot create image" error, and losing at the same time the real reason why the image was not created.

To debug, you can add a log in the Mage_Catalog_Model_Product_Attribute_Media_Api class, in the catch(Exception $e) (around line 186, to log what is the real Exception.

# Unable to find category
If you already sent the categories with the category export or the full export, but the Magento Connector Bundle still tells you that the category must be exported when you export products, there's a high chance that you spell the Magento URL and the WSDL URL differently between the export that sent categories and the product export. Sometimes, you've added a "/" at the end of the Magento URL parameter on one of the export and none on the other. It's enough so for the Magento Connector to believe it's a different Magento so the previously exported categories are not part of the same Magento.

# Storeview mapping form display as brut text

If you encounter a problem with the “Storeview mapping” form, like in the screenshot below:

*Storeview mapping form problem*:

![Storeview mapping form problem](./images/troubleshooting/storeview-trouble.png)

then you probably have forget to reinstall assets after installing the Magento connector. A simple

    php app/console pim:installer:assets
    
should settle the problem.
