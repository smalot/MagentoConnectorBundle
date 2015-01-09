<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

/**
 * Product writer used to send products in Api Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = [];
        foreach ($items as $item) {
            $products = array_merge($products, $item);
        }

        $mappedSkus = $this->getMappedSkus($products);
        try {
            $this->client->exportProducts($products);
        } catch (\SoapFault $e) {
            $failedProducts = json_decode($e->getMessage(), true);

            if (null !== $failedProducts) {
                $errors = $this->getFailedProducts($failedProducts, $mappedSkus);
                $this->manageFailedProducts($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }

    /**
     * Gives lines mapped to skus
     *
     * @param array $products
     *
     * @return array
     */
    protected function getMappedSkus(array $products)
    {
        $mappedSkus  = [];
        $previousSku = '';
        foreach ($products as $key => $product) {
            if (!empty($product['sku'])) {
                $mappedSkus[$key] = $product['sku'];
                $previousSku     = $product['sku'];
            } else {
                $mappedSkus[$key] = $previousSku;
            }
        }

        return $mappedSkus;
    }

    /**
     * Get failed products with their skus associated to errors
     * Returns [sku => ['errors', '']]
     *
     * @param array $errors
     * @param array $mappedSku
     *
     * @return array
     */
    protected function getFailedProducts(array $errors, array $mappedSku)
    {
        $failedProducts = [];
        foreach ($errors as $error => $failedRows) {
            foreach ($failedRows as $row) {
                $failedProducts[$mappedSku[$row]][] = $error;
            }
        }

        return $failedProducts;
    }

    /**
     * Add a warning for each failed product
     *
     * @param array $failedProducts
     */
    protected function manageFailedProducts(array $failedProducts)
    {
        foreach ($failedProducts as $sku => $errors) {
            foreach ($errors as $error) {
                $this->addWarning($error, [], [$sku]);
            }
        }
    }
}
