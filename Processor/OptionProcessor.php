<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Excetpion\NormalizeException;

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
     * {@inheritdoc}
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->optionNormalizer = $this->normalizerGuesser->getOptionNormalizer($this->getClientParameters());
        $magentoStoreViews      = $this->webservice->getStoreViewsList();

        $this->globalContext = array(
            'magentoStoreViews' => $magentoStoreViews,
            'storeViewMapping'  => $this->getComputedStoreViewMapping(),
            'channel'           => $this->channel,
            'defaultLocale'     => $this->defaultLocale
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($groupedOptions)
    {
        $this->beforeExecute();

        $attribute = $groupedOptions[0]->getAttribute();

        try {
            $optionsStatus = $this->webservice->getAttributeOptions($attribute->getCode());
        } catch (SoapCallException $e) {
            throw new InvalidItemException(
                sprintf(
                    'An error occurred during the retrieval of option list of the attribute "%s". This may be ' .
                    'due to the fact that "%s" attribute doesn\'t exist on Magento side. Please be sure that ' .
                    'this attribute is created (mannualy or by export) on Magento before options\' export. ' .
                    '(Original error : "%s")',
                    $attribute->getCode(),
                    $attribute->getCode(),
                    $e->getMessage()
                ),
                array($attribute)
            );
        }

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
            throw new InvalidItemException($e->getMessage(), array($product));
        }

        return $normalizedOption;
    }
}
