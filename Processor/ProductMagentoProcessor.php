<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\ImportExportBundle\Converter\MetricConverter;

/**
 * Magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ProductMagentoProcessor extends AbstractProductMagentoProcessor
{
    /**
     * @var metricConverter
     */
    protected $metricConverter;

    /**
     * @param ChannelManager           $channelManager
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param MetricConverter          $metricConverter
     */
    public function __construct(
        ChannelManager $channelManager,
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        MetricConverter $metricConverter
    ) {
        parent::__construct($channelManager, $webserviceGuesser, $normalizerGuesser);

        $this->metricConverter = $metricConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $processedItems = array();

        $this->beforeProcess();
        $magentoProducts = $this->webservice->getProductsStatus($items);

        $channel = $this->channelManager->getChannelByCode($this->channel);

        foreach ($items as $product) {
            $context = array_merge(
                $this->globalContext,
                array('attributeSetId' => $this->getAttributeSetId($product->getFamily()->getCode(), $product))
            );

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

            $this->metricConverter->convert($product, $channel);

            $processedItems[] = $this->normalizeProduct($product, $context);
        }

        return $processedItems;
    }

    /**
     * Normalize the given product
     *
     * @param ProductInterface $product [description]
     * @param array            $context The context
     *
     * @throws InvalidItemException If a normalization error occure
     * @return array                processed item
     */
    protected function normalizeProduct(ProductInterface $product, $context)
    {
        try {
            $processedItem = $this->productNormalizer->normalize(
                $product,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        }

        return $processedItem;
    }

    /**
     * Test if a product allready exist on magento platform
     *
     * @param ProductInterface $product         The product
     * @param array            $magentoProducts Magento products
     *
     * @return bool
     */
    protected function magentoProductExist(ProductInterface $product, $magentoProducts)
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
     * @param ProductInterface $product         The product
     * @param array            $magentoProducts Magento products
     *
     * @return bool
     */
    protected function attributeSetChanged(ProductInterface $product, $magentoProducts)
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
