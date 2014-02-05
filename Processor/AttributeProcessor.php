<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

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

    protected $attributeMapping;

    public function setAttributeMapping($attributeMapping)
    {
        $this->attributeMappingMerger->setMapping(json_decode($attributeMapping, true));

        return $this;
    }

    public function getAttributeMapping()
    {
        return json_encode($this->attributeMappingMerger->getMapping());
    }

    /**
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param LocaleManager            $localeManager
     * @param MappingMerger            $attributeMappingMerger
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MappingMerger $attributeMappingMerger
    ) {
        parent::__construct($webserviceGuesser, $normalizerGuesser, $localeManager);

        $this->attributeMappingMerger = $attributeMappingMerger;
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->attributeNormalizer = $this->normalizerGuesser->getAttributeNormalizer($this->getClientParameters());
        $this->globalContext['magentoStoreViews']        = $this->webservice->getStoreViewsList();
        $this->globalContext['magentoAttributes']        = $this->webservice->getAllAttributes();
        $this->globalContext['magentoAttributesOptions'] = $this->webservice->getAllAttributesOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function process($attribute)
    {
        $this->beforeExecute();

        $magentoAttributes = $this->webservice->getAllAttributes();

        $this->globalContext['create'] = !$this->magentoAttributeExists($attribute, $magentoAttributes);

        return $this->normalizeAttribute($attribute, $this->globalContext);
    }

    /**
     * Test if an attribute exist on magento
     * @param Attribute $attribute
     * @param array     $magentoAttributes
     *
     * @return boolean
     */
    protected function magentoAttributeExists(AttributeInterface $attribute, array $magentoAttributes)
    {
        return array_key_exists($attribute->getCode(), $magentoAttributes);
    }

    /**
     * Normalize the given attribute
     * @param Attribute $attribute
     * @param array     $context
     *
     * @throws InvalidItemException If a problem occured with the normalizer
     * @return array
     */
    protected function normalizeAttribute(AttributeInterface $attribute, array $context)
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

    protected function afterConfigurationSet()
    {
        $this->attributeMappingMerger->setParameters($this->getClientParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        error_log('get configuration fields');
        return array_merge(
            parent::getConfigurationFields(),
            $this->attributeMappingMerger->getConfigurationField()
        );
    }
}
