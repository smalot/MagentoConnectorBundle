<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap webservice that handle magento products
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWebservice extends AbstractWebservice
{
    /**
     * Get configurables status in magento (do they exist ?)
     * @param array $configurables the given configurables
     *
     * @return array
     */
    protected function getConfigurablesStatus(array $configurables = array())
    {
        $skus = $this->getConfigurablesIds($configurables);

        return $this->getStatusForSkus($skus);
    }

    /**
     * Serialize configurables id in csv
     * @param array $configurables The given configurables
     *
     * @return string The serialization result
     */
    protected function getConfigurablesIds(array $configurables = array())
    {
        $ids = array();

        foreach ($configurables as $configurable) {
            $ids[] = sprintf(
                AbstractWebservice::CONFIGURABLE_IDENTIFIER_PATTERN,
                $configurable['group']->getCode()
            );
        }

        return implode(',', $ids);
    }

    /**
     * Get products status in magento (do they exist ?)
     * @param array $products the given products
     *
     * @return array
     */
    public function getProductsStatus(array $products = array())
    {
        $skus = $this->getProductsIds($products);

        return $this->getStatusForSkus($skus);
    }

    /**
     * Get all images attached to a product
     *
     * @param string $sku The product sku
     *
     * @return array
     */
    public function getImages($sku)
    {
        try {
            $images = $this->client->call(
                self::SOAP_ACTION_PRODUCT_MEDIA_LIST,
                array(
                    $sku,
                    self::SOAP_DEFAULT_STORE_VIEW,
                    'sku'
                )
            );
        } catch (\Exception $e) {
            $images = array();
        }

        return $images;
    }

    /**
     * Send all product images
     *
     * @param array $images All images to send
     */
    public function sendImages($images)
    {
        foreach ($images as $image) {
            $this->client->addCall(
                array(
                    self::SOAP_ACTION_PRODUCT_MEDIA_CREATE,
                    $image
                )
            );
        }
    }

    /**
     * Add the call to update the given product part
     * @param array $productPart
     */
    public function updateProductPart($productPart)
    {
        $this->client->addCall(
            array(self::SOAP_ACTION_CATALOG_PRODUCT_UPDATE, $productPart)
        );
    }

    /**
     * Add a call for the given product part
     * @param array $productPart
     */
    public function sendProduct($productPart)
    {
        if (count($productPart) == self::CREATE_PRODUCT_SIZE ||
            count($productPart) == self::CREATE_CONFIGURABLE_SIZE &&
            $productPart[self::CREATE_CONFIGURABLE_SIZE - 1] != 'sku'
        ) {
            $resource = self::SOAP_ACTION_CATALOG_PRODUCT_CREATE;
        } else {
            $resource = self::SOAP_ACTION_CATALOG_PRODUCT_UPDATE;
        }

        $this->client->addCall(array($resource, $productPart));
    }

    /**
     * Delete a product association
     * @param array $productAssociation
     */
    public function removeProductAssociation(array $productAssociation)
    {
        $this->client->call(
            self::SOAP_ACTION_LINK_REMOVE,
            $productAssociation
        );
    }

    /**
     * Create a product association
     * @param array $productAssociation
     */
    public function createProductAssociation(array $productAssociation)
    {
        $this->client->call(
            self::SOAP_ACTION_LINK_CREATE,
            $productAssociation
        );
    }

    /**
     * Disable a product
     * @param string $productSku
     */
    public function disableProduct($productSku)
    {
        $this->client->call(
            self::SOAP_ACTION_CATALOG_PRODUCT_UPDATE,
            array(
                $productSku,
                array(
                    'status' => self::MAGENTO_STATUS_DISABLE
                )
            )
        );
    }

    /**
     * Serialize products id in csv
     * @param array $products The given products
     *
     * @return string The serialization result
     */
    protected function getProductsIds(array $products = array())
    {
        $ids = array();

        foreach ($products as $product) {
            $ids[] = $product->getIdentifier();
        }

        return implode(',', $ids);
    }

    /**
     * Get the products status for the given skus
     * @param array $skus
     *
     * @return array
     */
    protected function getStatusForSkus($skus)
    {
        if ($skus) {
            $filters = json_decode(
                json_encode(
                    array(
                        'complex_filter' => array(
                            array(
                                'key' => 'sku',
                                'value' => array('key' => 'in', 'value' => $skus)
                            )
                        )
                    )
                ),
                false
            );
        } else {
            $filters = array();
        }

        return $this->client->call(
            self::SOAP_ACTION_CATALOG_PRODUCT_LIST,
            $filters
        );
    }

    /**
     * Delete image for a given sku and a given filename
     * @param string $sku
     * @param string $imageFilename
     *
     * @return string
     */
    public function deleteImage($sku, $imageFilename)
    {
        return $this->client->call(
            self::SOAP_ACTION_PRODUCT_MEDIA_REMOVE,
            array(
                $sku,
                $imageFilename,
                'sku'
            )
        );
    }

    /**
     * Delete a product
     * @param string $productSku
     */
    public function deleteProduct($productSku)
    {
        $this->client->call(
            self::SOAP_ACTION_CATALOG_PRODUCT_DELETE,
            array(
                $productSku
            )
        );
    }
}
