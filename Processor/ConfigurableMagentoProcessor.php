<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Magento configurable processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ConfigurableMagentoProcessor extends AbstractMagentoProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $this->magentoWebservice      = $this->magentoWebserviceGuesser->getWebservice($this->getClientParameters());
        $productNormalizer            = $this->magentoNormalizerGuesser->getProductNormalizer(
            $this->getClientParameters(),
            $this->enabled,
            $this->visibility,
            $this->currency
        );
        $this->configurableNormalizer = $this->magentoNormalizerGuesser->getConfigurableNormalizer(
            $this->getClientParameters(),
            $productNormalizer
        );

        $magentoStoreViews        = $this->magentoWebservice->getStoreViewsList();
        $magentoAttributes        = $this->magentoWebservice->getAllAttributes();
        $magentoAttributesOptions = $this->magentoWebservice->getAllAttributesOptions();

        $processedItems = array();

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


        $magentoConfigurables = $this->magentoWebservice->getConfigurablesStatus($items);
        $magentoStoreViews    = $this->magentoWebservice->getStoreViewsList();

        foreach ($items as $configurable) {
            if (count($configurable['products']) == 0) {
                throw new InvalidItemException(
                    'The variant group is not associated to any products',
                    array($configurable)
                );
            }

            if ($this->magentoConfigurableExist($configurable, $magentoConfigurables)) {
                $context['create']         = false;
                $context['attributeSetId'] = 0;
            } else {
                $context['create'] = true;
                $groupFamily       = $this->getGroupFamily($configurable);

                $context['attributeSetId'] = $this->getAttributeSetId($groupFamily->getCode(), $configurable);
            }

            $processedItems[] = $this->normalizeConfigurable($configurable, $context);
        }

        print_r($processedItems);

        return $processedItems;
    }

    /**
     * Normalize the given configurable
     *
     * @param  array $configurable
     * @param  array $context The context
     * @return array processed item
     */
    protected function normalizeConfigurable($configurable, $context)
    {
        try {
            $processedItem = $this->configurableNormalizer->normalize($configurable, 'MagentoArray', $context);
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($configurable));
        }

        return $processedItem;
    }

    /**
     * Test if a configurable allready exist on magento platform
     *
     * @param  array   $configurable         The configurable
     * @param  array   $magentoConfigurables Magento configurables
     * @return bool
     */
    protected function magentoConfigurableExist($configurable, $magentoConfigurables)
    {
        foreach ($magentoConfigurables as $magentoConfigurable) {

            if (
                $magentoConfigurable['sku'] == sprintf(
                    MagentoWebservice::CONFIGURABLE_IDENTIFIER_PATTERN, $configurable['group']->getCode()
                )
            ) {
                return true;
            }
        }

        return false;
    }

    protected function getGroupFamily($configurable)
    {
        $groupFamily = $configurable['products'][0]->getFamily();

        foreach ($configurable['products'] as $product) {
            if ($groupFamily != $product->getFamily()) {
                throw new InvalidItemException('Your variant group contains products from different ' .
                    'families. Magento cannot handle configurable products with heterogen attribute sets');
            }
        }

        return $groupFamily;
    }
}
