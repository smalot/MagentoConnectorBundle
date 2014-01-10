<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\CatalogBundle\Model\Product;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;

/**
 * Magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ProductMagentoProcessor extends AbstractMagentoProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $this->magentoWebservice = $this->magentoWebserviceGuesser->getWebservice($this->getClientParameters());
        $this->productNormalizer = $this->magentoNormalizerGuesser->getProductNormalizer(
            $this->getClientParameters(),
            $this->enabled,
            $this->visibility,
            $this->currency
        );

        $processedItems = array();

        $magentoProducts          = $this->magentoWebservice->getProductsStatus($items);
        $magentoStoreViews        = $this->magentoWebservice->getStoreViewsList();
        $magentoAttributes        = $this->magentoWebservice->getAllAttributes();
        $magentoAttributesOptions = $this->magentoWebservice->getAllAttributesOptions();

        $context = array(
            'magentoStoreViews'        => $magentoStoreViews,
            'defaultLocale'            => $this->defaultLocale,
            'channel'                  => $this->channel,
            'magentoAttributes'        => $magentoAttributes,
            'magentoAttributesOptions' => $magentoAttributesOptions,
            'currency'                 => $this->currency,
            'storeViewMapping'         => $this->getComputedStoreViewMapping(),
            'website'                  => $this->website
        );

        $this->metricConverter->convert($items, $this->channelManager->getChannelByCode($this->channel));

        foreach ($items as $product) {
            $context['attributeSetId'] = $this->getAttributeSetId($product->getFamily()->getCode(), $product);

            if ($this->magentoProductExist($product, $magentoProducts)) {
                if ($this->attributeSetChanged($product, $magentoProducts)) {
                    throw new InvalidItemException(
                        'The product family has changed of this product. This modification cannot be applied to ' .
                        'magento. In order to change the family of this product, please manualy delete this product ' .
                        'in magento and re-run this connector.',
                        array($product)
                    );
                }

                $context['create'] = false;
            } else {
                $context['create'] = true;
            }

            $processedItems[] = $this->normalizeProduct($product, $context);
        }

        return $processedItems;
    }

    /**
     * Normalize the given product
     *
     * @param  Product $product [description]
     * @param  array   $context The context
     * @throws InvalidItemException If a normalization error occure
     * @return array processed item
     */
    protected function normalizeProduct(Product $product, $context)
    {
        try {
            $processedItem = $this->productNormalizer->normalize($product, 'MagentoArray', $context);
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        }

        return $processedItem;
    }

    /**
     * Test if a product allready exist on magento platform
     *
     * @param  Product $product         The product
     * @param  array   $magentoProducts Magento products
     * @return bool
     */
    protected function magentoProductExist(Product $product, $magentoProducts)
    {
        foreach ($magentoProducts as $magentoProduct) {

            if ($magentoProduct['sku'] == $product->getIdentifier()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test if the product attribute set changed
     *
     * @param  Product $product         The product
     * @param  array   $magentoProducts Magento products
     * @return bool
     */
    protected function attributeSetChanged(Product $product, $magentoProducts)
    {
        foreach ($magentoProducts as $magentoProduct) {
            if ($magentoProduct['sku'] == $product->getIdentifier() &&
                $magentoProduct['set'] != $this->getAttributeSetId($product->getFamily()->getCode(), $product)
            ) {
                return true;
            }
        }

        return false;
    }
}
