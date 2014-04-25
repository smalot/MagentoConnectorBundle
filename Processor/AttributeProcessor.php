<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\CatalogBundle\Entity\Attribute;

/**
 * Magento attributes processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeProcessor extends AbstractProcessor
{
    /**
     * @var MappingMerger
     */
    protected $attributeMappingMerger;

    /**
     * @var string
     */
    protected $attributeMapping = '';

    /**
     * Set attribute mapping
     * @param string $attributeMapping
     *
     * @return AttributeProcessor
     */
    public function setAttributeMapping($attributeMapping)
    {
        $this->attributeMappingMerger->setMapping(json_decode($attributeMapping, true));

        return $this;
    }

    /**
     * Get attribute mapping
     * @return string
     */
    public function getAttributeMapping()
    {
        return json_encode($this->attributeMappingMerger->getMapping()->toArray());
    }

    /**
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param LocaleManager            $localeManager
     * @param MappingMerger            $storeViewMappingMerger
     * @param MappingMerger            $attributeMappingMerger
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MappingMerger $storeViewMappingMerger,
        MappingMerger $attributeMappingMerger
    ) {
        parent::__construct($webserviceGuesser, $normalizerGuesser, $localeManager, $storeViewMappingMerger);

        $this->attributeMappingMerger = $attributeMappingMerger;
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $magentoStoreViews = $this->webservice->getStoreViewsList();

        $this->attributeNormalizer = $this->normalizerGuesser->getAttributeNormalizer($this->getClientParameters());
        $this->globalContext['magentoAttributes']        = $this->webservice->getAllAttributes();
        $this->globalContext['magentoAttributesOptions'] = $this->webservice->getAllAttributesOptions();
        $this->globalContext['attributeMapping']         = $this->attributeMappingMerger->getMapping();
        $this->globalContext['magentoStoreViews']        = $magentoStoreViews;
    }

    /**
     * {@inheritdoc}
     */
    public function process($attribute)
    {
        $this->beforeExecute();
        $magentoAttributes = $this->webservice->getAllAttributes();

        $this->globalContext['create'] = !$this->magentoAttributeExists($attribute, $magentoAttributes);
        $result = [$attribute, $this->normalizeAttribute($attribute, $this->globalContext)];

        return $result;
    }

    /**
     * Test if an attribute exist on magento
     * @param Attribute $attribute
     * @param array     $magentoAttributes
     *
     * @return boolean
     */
    protected function magentoAttributeExists(Attribute $attribute, array $magentoAttributes)
    {
        return array_key_exists(
            $this->attributeMappingMerger->getMapping()->getTarget($attribute->getCode()),
            $magentoAttributes
        );
    }

    /**
     * Normalize the given attribute
     * @param Attribute $attribute
     * @param array     $context
     *
     * @throws InvalidItemException If a problem occured with the normalizer
     * @return array
     */
    protected function normalizeAttribute(Attribute $attribute, array $context)
    {
        try {
            $processedItem = $this->attributeNormalizer->normalize(
                $attribute,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($attribute));
        }

        return $processedItem;
    }

    /**
     * Called after the configuration is set
     */
    protected function afterConfigurationSet()
    {
        parent::afterConfigurationSet();

        $this->attributeMappingMerger->setParameters($this->getClientParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            $this->attributeMappingMerger->getConfigurationField()
        );
    }
}
