<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebservice;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager;

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
     * @var ConfigurableNormalizer
     */
    protected $configurableNormalizer;

    /**
     * Function called before all process
     */
    protected function beforeProcess()
    {
        parent::beforeProcess();

        $priceMappingManager          = new PriceMappingManager($this->defaultLocale, $this->currency);
        $this->configurableNormalizer = $this->magentoNormalizerGuesser->getConfigurableNormalizer(
            $this->getClientParameters(),
            $this->productNormalizer,
            $priceMappingManager
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $processedItems = array();

        $this->beforeProcess();

        $magentoConfigurables = $this->magentoWebservice->getConfigurablesStatus($items);

        foreach ($items as $configurable) {
            if (count($configurable['products']) == 0) {
                throw new InvalidItemException(
                    'The variant group is not associated to any products',
                    array($configurable)
                );
            }

            if ($this->magentoConfigurableExist($configurable, $magentoConfigurables)) {
                $context = array_merge(
                    $this->globalContext,
                    array('attributeSetId' => 0, 'create' => false)
                );
            } else {
                $groupFamily = $this->getGroupFamily($configurable);
                $context     = array_merge(
                    $this->globalContext,
                    array(
                        'attributeSetId' => $this->getAttributeSetId($groupFamily->getCode(), $configurable),
                        'create'         => true
                    )
                );
            }

            $processedItems[] = $this->normalizeConfigurable($configurable, $context);
        }

        return $processedItems;
    }

    /**
     * Normalize the given configurable
     *
     * @param  array                $configurable
     * @param  array                $context      The context
     * @throws InvalidItemException If a normalization error occured
     * @return array                processed item
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
     * @param  array $configurable         The configurable
     * @param  array $magentoConfigurables Magento configurables
     * @return bool
     */
    protected function magentoConfigurableExist($configurable, $magentoConfigurables)
    {
        foreach ($magentoConfigurables as $magentoConfigurable) {

            if ($magentoConfigurable['sku'] == sprintf(
                MagentoWebservice::CONFIGURABLE_IDENTIFIER_PATTERN,
                $configurable['group']->getCode()
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the family of the given configurable
     * @param  array                $configurable
     * @throws InvalidItemException If there are two products with different families
     * @return Family
     */
    protected function getGroupFamily($configurable)
    {
        $groupFamily = $configurable['products'][0]->getFamily();

        foreach ($configurable['products'] as $product) {
            if ($groupFamily != $product->getFamily()) {
                throw new InvalidItemException(
                    'Your variant group contains products from different families. Magento cannot handle ' .
                    'configurable products with heterogen attribute sets'
                );
            }
        }

        return $groupFamily;
    }
}
