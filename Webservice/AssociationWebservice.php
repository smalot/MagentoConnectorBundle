<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * A magento soap webservice that handle magento associations
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociationWebservice extends AbstractWebservice
{
    /**
     * Get associations status
     * @param ProductInterface $product
     *
     * @return array
     */
    public function getAssociationsStatus(ProductInterface $product)
    {
        $associationStatus = array();
        $sku               = (string) $product->getIdentifier();

        $associationStatus['up_sell'] = $this->client->call(
            self::SOAP_ACTION_LINK_LIST,
            array(
                'up_sell',
                $sku,
                'sku'
            )
        );

        $associationStatus['cross_sell'] = $this->client->call(
            self::SOAP_ACTION_LINK_LIST,
            array(
                'cross_sell',
                $sku,
                'sku'
            )
        );

        $associationStatus['related'] = $this->client->call(
            self::SOAP_ACTION_LINK_LIST,
            array(
                'related',
                $sku,
                'sku'
            )
        );

        $associationStatus['grouped'] = $this->client->call(
            self::SOAP_ACTION_LINK_LIST,
            array(
                'grouped',
                $sku,
                'sku'
            )
        );

        return $associationStatus;
    }
}