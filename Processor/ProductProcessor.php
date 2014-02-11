<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\AssociationTypeManager;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\CurrencyManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;

/**
 * Magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductProcessor extends AbstractProductProcessor
{
    /**
     * @var metricConverter
     */
    protected $metricConverter;

    /**
     * @var AssociationTypeManager
     */
    protected $associationTypeManager;

    /**
     * @var string
     */
    protected $pimGrouped;

    /**
     * Get pimGrouped
     * @return string
     */
    public function getPimGrouped()
    {
        return $this->pimGrouped;
    }

    /**
     * Set pimGrouped
     * @param string $pimGrouped
     *
     * @return ProductProcessor
     */
    public function setPimGrouped($pimGrouped)
    {
        $this->pimGrouped = $pimGrouped;

        return $this;
    }

    /**
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param LocaleManager            $localeManager
     * @param MappingMerger            $storeViewMappingMerger
     * @param CurrencyManager          $currencyManager
     * @param ChannelManager           $channelManager
     * @param MappingMerger            $categoryMappingMerger
     * @param MetricConverter          $metricConverter
     * @param AssociationTypeManager   $associationTypeManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MappingMerger $storeViewMappingMerger,
        CurrencyManager $currencyManager,
        ChannelManager $channelManager,
        MappingMerger $categoryMappingMerger,
        MetricConverter $metricConverter,
        AssociationTypeManager $associationTypeManager
    ) {
        parent::__construct(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $storeViewMappingMerger,
            $currencyManager,
            $channelManager,
            $categoryMappingMerger
        );

        $this->metricConverter        = $metricConverter;
        $this->associationTypeManager = $associationTypeManager;
    }

    /**
     * Function called before all process
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->globalContext['pimGrouped'] = $this->pimGrouped;
    }

    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $this->beforeExecute();

        $processedItems = array();

        $magentoProducts = $this->webservice->getProductsStatus($items);

        $channel = $this->channelManager->getChannelByCode($this->channel);

        foreach ($items as $product) {
            $context = array_merge(
                $this->globalContext,
                array('attributeSetId' => $this->getAttributeSetId($product->getFamily()->getCode(), $product))
            );

            if ($this->magentoProductExists($product, $magentoProducts)) {
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
     * Test if a product allready exists on magento platform
     *
     * @param ProductInterface $product         The product
     * @param array            $magentoProducts Magento products
     *
     * @return bool
     */
    protected function magentoProductExists(ProductInterface $product, $magentoProducts)
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

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'pimGrouped' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices' => $this->associationTypeManager->getAssociationTypeChoices()
                    )
                )
            )
        );
    }
}
