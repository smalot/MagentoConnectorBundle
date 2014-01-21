<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\CatalogBundle\Model\Association;

/**
 * Magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ProductAssociationProcessor extends AbstractProductProcessor
{
    const MAGENTO_UP_SELL = 'up_sell';
    const PIM_UP_SELL     = 'UPSELL';
    const MAGENTO_CROSS_SELL = 'up_sell';
    const PIM_X_SELL     = 'UPSELL';

    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $processedItems = array();

        $this->beforeProcess();

        $productAssociationCalls = array('remove' => array(), 'create' => array());

        foreach ($items as $product) {
            $productAssociationCalls['remove'] += $this->getRemoveCallsForProduct(
                $product,
                $this->webservice->getAssociationsStatus($product)
            );
            $productAssociationCalls['create'] += $this->getCreateCallsForProduct($product);
        }

        var_dump($productAssociationCalls);

        return $productAssociationCalls;
    }

    protected function getCreateCallsForProduct(ProductInterface $product)
    {
        $createAssociationCalls = array();

        foreach ($product->getAssociations() as $productAssociation) {
            $createAssociationCalls += $this->getCreateCalls($product, $productAssociation);
        }

        return $createAssociationCalls;
    }

    protected function getCreateCalls(ProductInterface $product, Association $productAssociation)
    {
        $createAssociationCalls = array();

        $associationType = $productAssociation->getAssociationType()->getCode();

        if (in_array($associationType, array_keys($this->getAssociationCodeMapping()))) {
            foreach ($productAssociation->getProducts() as $associatedProduct) {
                $createAssociationCalls[] = array(
                    'type'          => $this->getAssociationCodeMapping()[$associationType],
                    'product'       => (string) $product->getIdentifier(),
                    'linkedProduct' => (string) $associatedProduct->getIdentifier()
                );
            }
        }

        return $createAssociationCalls;
    }

    protected function getRemoveCallsForProduct(ProductInterface $product, array $associationStatus)
    {
        $removeAssociationCalls = array();

        foreach ($associationStatus as $associationType => $associatedProducts) {
            foreach ($associatedProducts as $associatedProduct) {
                $removeAssociationCalls[] = array(
                    'type'          => $associationType,
                    'product'       => (string) $product->getIdentifier(),
                    'linkedProduct' => (string) $associatedProduct['sku']
                );
            }
        }

        return $removeAssociationCalls;
    }

    protected function getAssociationCodeMapping()
    {
        return array(
            self::PIM_UP_SELL => self::MAGENTO_UP_SELL,
            self::PIM_X_SELL  => self::MAGENTO_CROSS_SELL
        );
    }
}
