<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AbstractNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

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
     * @var GroupManager
     */
    protected $groupManager;

    /** @var string */
    protected $attributeCodeMapping;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param ProductNormalizerGuesser            $normalizerGuesser
     * @param LocaleManager                       $localeManager
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     * @param GroupManager                        $groupManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        GroupManager $groupManager
    ) {
        parent::__construct(
            $webserviceGuesser,
            $normalizerGuesser,
            $localeManager,
            $clientParametersRegistry
        );

        $this->groupManager = $groupManager;
    }

    /**
     * Get raw (json encoded) attribute code mapping
     *
     * @return string
     */
    public function getAttributeCodeMapping()
    {
        if (empty($this->attributeCodeMapping)) {
            return json_encode([]);
        } else {
            return $this->attributeCodeMapping;
        }
    }

    /**
     * Set raw (json encoded) attribute code mapping
     *
     * @param string $attributeCodeMapping
     *
     * @return AttributeReader
     */
    public function setAttributeCodeMapping($attributeCodeMapping)
    {
        $this->attributeCodeMapping = $attributeCodeMapping;

        return $this;
    }

    /**
     * Get decoded attribute mapping
     *
     * @return array
     */
    public function getDecodedAttributeCodeMapping()
    {
        return json_decode($this->attributeCodeMapping, true);
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
        $this->globalContext['attributeCodeMapping']     = $this->getDecodedAttributeCodeMapping();
        $this->globalContext['magentoStoreViews']        = $magentoStoreViews;
        $this->globalContext['axisAttributes']           = $this->getAxisAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function process($attribute)
    {
        $this->beforeExecute();
        $magentoAttributes = $this->globalContext['magentoAttributes'];

        $this->globalContext['create'] = !$this->magentoAttributeExists($attribute, $magentoAttributes);
        $result = [$attribute, $this->normalizeAttribute($attribute, $this->globalContext)];

        return $result;
    }

    /**
     * Test if an attribute exist on magento
     * @param AbstractAttribute $attribute
     * @param array             $magentoAttributes
     *
     * @return boolean
     */
    protected function magentoAttributeExists(AbstractAttribute $attribute, array $magentoAttributes)
    {
        $attributeCodeMapping = $this->getDecodedAttributeCodeMapping();

        if (isset($attributeCodeMapping[$attribute->getCode()])) {
            $magentoAttributeCode = $attributeCodeMapping[$attribute->getCode()];
        } else {
            $magentoAttributeCode = $attribute->getCode();
        }

        $magentoAttributeCode = strtolower($magentoAttributeCode);

        return array_key_exists(
            $magentoAttributeCode,
            $magentoAttributes
        );
    }

    /**
     * Normalize the given attribute
     * @param AbstractAttribute $attribute
     * @param array             $context
     *
     * @throws InvalidItemException If a problem occurred with the normalizer
     * @return array
     */
    protected function normalizeAttribute(AbstractAttribute $attribute, array $context)
    {
        try {
            $processedItem = $this->attributeNormalizer->normalize(
                $attribute,
                AbstractNormalizer::MAGENTO_FORMAT,
                $context
            );
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), [$attribute]);
        }

        return $processedItem;
    }

    /**
     * Get attribute axis
     *
     * @return array
     */
    protected function getAxisAttributes()
    {
        $result = [];

        $attributeAxis = $this->groupManager->getRepository()->getAxisAttributes();

        foreach ($attributeAxis as $attribute) {
            $result[] = $attribute['code'];
        }

        return array_unique($result);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        $dataTargets = array_merge(
            ['route' => 'magento-attributes'],
            $this->getMagentoParamsForMapping()
        );

        return array_merge(
            parent::getConfigurationFields(),
            [
                'attributeCodeMapping' => [
                    'type'    => 'textarea',
                    'options' => [
                        'label' => 'pim_magento_connector.export.attributeCodeMapping.label',
                        'help'  => 'pim_magento_connector.export.attributeCodeMapping.help',
                        'required' => false,
                        'attr'     => [
                            'class' => 'mapping-field',
                            'data-sources' => json_encode([
                                'route' => 'pim-attributes'
                            ]),
                            'data-targets' => json_encode($dataTargets),
                            'data-name'   => 'attributeCode'
                        ]
                    ]
                ]
            ]
        );
    }
}
