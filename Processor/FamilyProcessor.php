<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\CatalogBundle\Entity\Family;

/**
 * Magento family processor
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyProcessor extends AbstractProcessor
{
    /**
     * @var MappingMerger
     */
    protected $familyMappingMerger;

    /**
     * @var string
     */
    protected $familyMapping = '';

    /**
     * Set attribute set mapping
     * @param $familyMapping
     *
     * @return FamilyProcessor
     */
    public function setAttributeSetMapping($familyMapping)
    {
        $this->attributeSetMappingMerger->setMapping(json_decode($familyMapping, true));

        return $this;
    }

    /**
     * Get attribute set mapping
     * @return string
     */
    public function getAttributeSetMapping()
    {
        return json_encode($this->attributeSetMappingMerger->getMapping()->toArray());
    }

    /**
     * @param WebserviceGuesser $webserviceGuesser
     * @param NormalizerGuesser $normalizerGuesser
     * @param LocaleManager     $localeManager
     * @param MappingMerger     $storeViewMappingMerger
     * @param MappingMerger     $attributeSetMappingMerger
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager     $localeManager,
        MappingMerger     $storeViewMappingMerger,
        MappingMerger     $attributeSetMappingMerger
    ) {
        parent::__construct($webserviceGuesser, $normalizerGuesser, $localeManager, $storeViewMappingMerger);

        $this->attributeSetMappingMerger = $attributeSetMappingMerger;
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $magentoStoreViews = $this->webservice->getStoreViewsList();

        $this->familyNormalizer                        = $this->normalizerGuesser
            ->getFamilyNormalizer($this->getClientParameters());
        $this->globalContext['magentoFamilies']        = $this->webservice->getAttributeSetList();
        $this->globalContext['familyMapping']          = $this->familyMappingMerger->getMapping();
        $this->globalContext['magentoStoreViews']      = $magentoStoreViews;
    }

    /**
     * {@inheritdoc}
     */
    public function process($attributeSet)
    {
        $this->beforeExecute();

        $magentoAttributesSet = $this->webservice->getAttributeSetList();

        $this->globalContext['create'] = !$this->magentoAttributeSetExists($attributeSet, $magentoAttributesSet);

        return $this->normalizeFamily($attributeSet, $this->globalContext);
    }

    /**
     * Test if an attribute set exist on magento
     * @param Family $family               Family of attribute
     * @param array  $magentoAttributesSet Attribute sets from magento
     *
     * @return boolean Return true if the family exist on magento
     */
    protected function magentoAttributeSetExists(Family $family, array $magentoAttributesSet)
    {
        return array_key_exists(
            $this->attributeSetMappingMerger->getMapping()->getTarget($family->getCode()),
            $magentoAttributesSet
        );
    }

    /**
     * Normalize the given family
     * @param Family $family        Family of attribute
     * @param array  $context
     *
     * @throws InvalidItemException If a problem occurred with the normalizer
     * @return array
     */
    protected function normalizeFamily(Family $family, array $context)
    {
        try {
            $processedItem = $this->familyNormalizer->normalize(
                $family,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($family));
        }

        return $processedItem;
    }

    /**
     * Called after the configuration is set
     */
    protected function afterConfigurationSet()
    {
        parent::afterConfigurationSet();

        $this->attributeSetMappingMerger->setParameters($this->getClientParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            $this->attributeSetMappingMerger->getConfigurationField()
        );
    }
}
