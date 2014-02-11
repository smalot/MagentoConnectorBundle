<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Manager\CurrencyManager;

/**
 * Magento configurable processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurableProcessor extends AbstractProductProcessor
{
    /**
     * @var ConfigurableNormalizer
     */
    protected $configurableNormalizer;

    /**
     * @var GroupManager
     */
    protected $groupManager;

    /**
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param LocaleManager            $localeManager
     * @param MappingMerger            $storeViewMappingMerger
     * @param CurrencyManager          $currencyManager
     * @param ChannelManager           $channelManager
     * @param MappingMerger            $categoryMappingMerger
     * @param GroupManager             $groupManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MappingMerger $storeViewMappingMerger,
        CurrencyManager $currencyManager,
        ChannelManager $channelManager,
        MappingMerger $categoryMappingMerger,
        GroupManager $groupManager
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

        $this->groupManager = $groupManager;
    }

    /**
     * Function called before all process
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $priceMappingManager          = new PriceMappingManager($this->defaultLocale, $this->currency);
        $this->configurableNormalizer = $this->normalizerGuesser->getConfigurableNormalizer(
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
        $this->beforeExecute();

        $processedItems = array();

        $groupsIds            = $this->getGroupRepository()->getVariantGroupIds();
        $configurables        = $this->getProductsForGroups($items, $groupsIds);
        $magentoConfigurables = $this->webservice->getConfigurablesStatus($configurables);

        foreach ($configurables as $configurable) {
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
     * @param array $configurable The given configurable
     * @param array $context      The context
     *
     * @throws InvalidItemException If a normalization error occured
     * @return array                processed item
     */
    protected function normalizeConfigurable($configurable, $context)
    {
        try {
            $processedItem = $this->configurableNormalizer->normalize(
                $configurable,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($configurable['group']));
        } catch (SoapCallException $e) {
            throw new InvalidItemException($e->getMessage(), array($configurable['group']));
        }

        return $processedItem;
    }

    /**
     * Test if a configurable allready exist on magento platform
     *
     * @param array $configurable         The configurable
     * @param array $magentoConfigurables Magento configurables
     *
     * @return bool
     */
    protected function magentoConfigurableExist($configurable, $magentoConfigurables)
    {
        foreach ($magentoConfigurables as $magentoConfigurable) {

            if ($magentoConfigurable['sku'] == sprintf(
                Webservice::CONFIGURABLE_IDENTIFIER_PATTERN,
                $configurable['group']->getCode()
            )) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the family of the given configurable
     * @param array $configurable
     *
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

    /**
     * Get products association for each groups
     * @param array $products
     * @param array $groupsIds
     *
     * @return array
     */
    protected function getProductsForGroups(array $products, array $groupsIds)
    {
        $groups = array();

        foreach ($products as $product) {
            foreach ($product->getGroups() as $group) {
                $groupId = $group->getId();

                if (in_array($groupId, $groupsIds)) {
                    if (!isset($groups[$groupId])) {
                        $groups[$groupId] = array(
                            'group'    => $group,
                            'products' => array()
                        );
                    }

                    $groups[$groupId]['products'][] = $product;
                }
            }
        }

        return $groups;
    }

    /**
     * Get the group repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getGroupRepository()
    {
        return $this->groupManager->getRepository();
    }
}
