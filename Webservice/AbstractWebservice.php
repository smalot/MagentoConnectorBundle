<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Abstract class to handle interactions with magento webservice api
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractWebservice
{
    const SOAP_ACTION_CATALOG_PRODUCT_CREATE        = 'catalog_product.create';
    const SOAP_ACTION_CATALOG_PRODUCT_UPDATE        = 'catalog_product.update';
    const SOAP_ACTION_CATALOG_PRODUCT_DELETE        = 'catalog_product.delete';
    const SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE = 'catalog_product.currentStore';
    const SOAP_ACTION_CATALOG_PRODUCT_LIST          = 'catalog_product.list';
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_OPTIONS     = 'catalog_product_attribute.options';
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST    = 'product_attribute_set.list';
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_LIST        = 'catalog_product_attribute.list';
    const SOAP_ACTION_ATTRIBUTE_OPTION_LIST         = 'catalog_product_attribute.options';
    const SOAP_ACTION_ATTRIBUTE_OPTION_ADD          = 'catalog_product_attribute.addOption';
    const SOAP_ACTION_ATTRIBUTE_OPTION_REMOVE       = 'catalog_product_attribute.removeOption';
    const SOAP_ACTION_ATTRIBUTE_CREATE              = 'product_attribute.create';
    const SOAP_ACTION_ATTRIBUTE_UPDATE              = 'product_attribute.update';
    const SOAP_ACTION_ATTRIBUTE_REMOVE              = 'product_attribute.remove';
    const SOAP_ACTION_STORE_LIST                    = 'store.list';
    const SOAP_ACTION_PRODUCT_MEDIA_CREATE          = 'catalog_product_attribute_media.create';
    const SOAP_ACTION_PRODUCT_MEDIA_LIST            = 'catalog_product_attribute_media.list';
    const SOAP_ACTION_PRODUCT_MEDIA_REMOVE          = 'catalog_product_attribute_media.remove';
    const SOAP_ACTION_CATEGORY_TREE                 = 'catalog_category.tree';
    const SOAP_ACTION_CATEGORY_CREATE               = 'catalog_category.create';
    const SOAP_ACTION_CATEGORY_UPDATE               = 'catalog_category.update';
    const SOAP_ACTION_CATEGORY_DELETE               = 'catalog_category.delete';
    const SOAP_ACTION_CATEGORY_MOVE                 = 'catalog_category.move';
    const SOAP_ACTION_LINK_LIST                     = 'catalog_product_link.list';
    const SOAP_ACTION_LINK_REMOVE                   = 'catalog_product_link.remove';
    const SOAP_ACTION_LINK_CREATE                   = 'catalog_product_link.assign';

    const SOAP_DEFAULT_STORE_VIEW                   = 'default';
    const IMAGES                                    = 'images';
    const SOAP_ATTRIBUTE_ID                         = 'attribute_id';
    const SMALL_IMAGE                               = 'small_image';
    const BASE_IMAGE                                = 'image';
    const THUMBNAIL                                 = 'thumbnail';
    const SELECT                                    = 'select';
    const MULTI_SELECT                              = 'multiselect';

    const MAXIMUM_CALLS            = 1;
    const CREATE_PRODUCT_SIZE      = 5;
    const CREATE_CONFIGURABLE_SIZE = 4;

    const CONFIGURABLE_IDENTIFIER_PATTERN = 'conf-%s';

    const MAGENTO_STATUS_DISABLE = 2;

    const ADMIN_STOREVIEW = 0;

    protected $client;

    protected $magentoAttributeSets;
    protected $magentoStoreViewList;

    protected $attributeList       = array();
    protected $attributes          = array();
    protected $attributeSetList    = array();
    protected $attributeOptionList = array();
    protected $categories          = array();

    /**
     * Constructor
     * @param MagentoSoapClient $client
     */
    public function __construct(MagentoSoapClient $client)
    {
        $this->client = $client;
    }
}
