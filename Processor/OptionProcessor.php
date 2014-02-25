<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MappingMerger;

/**
 * Magento option processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionProcessor extends AbstractProcessor
{
    /**
     * @var OptionNormalizer
     */
    protected $optionNormalizer;

    /**
     * @var MappingMerger
     */
    protected $attributeMappingMerger;

    /**
     * @var string
     */
    protected $attributeMapping;

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
     * {@inheritdoc}
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->optionNormalizer = $this->normalizerGuesser->getOptionNormalizer($this->getClientParameters());

        $magentoStoreViews = $this->webservice->getStoreViewsList();

        $this->globalContext['magentoStoreViews'] = $magentoStoreViews;
        $this->globalContext['attributeMapping']  = $this->attributeMappingMerger->getMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function process($groupedOptions)
    {
        $this->beforeExecute();

        $attribute = $groupedOptions[0]->getAttribute();
        $attributeCode = $this->globalContext['attributeMapping']->getTarget($attribute->getCode());

        try {
            $optionsStatus = $this->webservice->getAttributeOptions($attributeCode);
        } catch (SoapCallException $e) {
            throw new InvalidItemException(
                sprintf(
                    'An error occurred during the retrieval of option list of the attribute "%s". This may be ' .
                    'due to the fact that "%s" attribute doesn\'t exist on Magento side. Please be sure that ' .
                    'this attribute is created (mannualy or by export) on Magento before options\' export. ' .
                    '(Original error : "%s")',
                    $attributeCode,
                    $attributeCode,
                    $e->getMessage()
                ),
                array($attribute)
            );
        }

        $this->globalContext['attributeCode'] = $attributeCode;

        $normalizedOptions = array();

        foreach ($groupedOptions as $option) {
            if (!array_key_exists($option->getCode(), $optionsStatus)) {
                $normalizedOptions[] = $this->getNormalizedOption($option, $this->globalContext);
            }
        }

        return $normalizedOptions;
    }

    /**
     * Get the normalized
     * @param AttributeOption $option
     * @param array           $context
     *
     * @return array
     */
    protected function getNormalizedOption(AttributeOption $option, array $context)
    {
        try {
            $normalizedOption = $this->optionNormalizer->normalize(
                $option,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($option));
        }

        return $normalizedOption;
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
