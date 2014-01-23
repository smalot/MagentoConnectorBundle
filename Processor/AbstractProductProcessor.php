<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductProcessor extends AbstractProcessor
{
    const MAGENTO_VISIBILITY_CATALOG_SEARCH = 4;

    /**
     * @var ProductNormalizer
     */
    protected $productNormalizer;

    /**
     * @var string
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $currency;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var integer
     */
    protected $visibility = self::MAGENTO_VISIBILITY_CATALOG_SEARCH;

    /**
     * get currency
     *
     * @return string currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency
     *
     * @param string $currency currency
     *
     * @return AbstractProcessor
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * get enabled
     *
     * @return string enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set enabled
     *
     * @param string $enabled enabled
     *
     * @return AbstractProcessor
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * get visibility
     *
     * @return string visibility
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set visibility
     *
     * @param string $visibility visibility
     *
     * @return AbstractProcessor
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @var string
     */
    protected $rootCategoryMapping = '';

    /**
     * get rootCategoryMapping
     *
     * @return string rootCategoryMapping
     */
    public function getRootCategoryMapping()
    {
        return $this->rootCategoryMapping;
    }

    /**
     * Set rootCategoryMapping
     *
     * @param string $rootCategoryMapping rootCategoryMapping
     *
     * @return AbstractProcessor
     */
    public function setRootCategoryMapping($rootCategoryMapping)
    {
        $this->rootCategoryMapping = $rootCategoryMapping;

        return $this;
    }

    /**
     * Get computed storeView mapping (string to array)
     * @return array
     */
    protected function getComputedRootCategoryMapping()
    {
        return $this->getComputedMapping($this->rootCategoryMapping);
    }

    /**
     * Function called before all process
     */
    protected function beforeExecute()
    {
        parent::beforeExecute();

        $this->productNormalizer = $this->normalizerGuesser->getProductNormalizer(
            $this->getClientParameters(),
            $this->enabled,
            $this->visibility,
            $this->currency,
            $this->soapUrl
        );

        $magentoStoreViews        = $this->webservice->getStoreViewsList();
        $magentoAttributes        = $this->webservice->getAllAttributes();
        $magentoAttributesOptions = $this->webservice->getAllAttributesOptions();

        $this->globalContext = array(
            'defaultLocale'            => $this->defaultLocale,
            'channel'                  => $this->channel,
            'currency'                 => $this->currency,
            'website'                  => $this->website,
            'magentoStoreViews'        => $magentoStoreViews,
            'magentoAttributes'        => $magentoAttributes,
            'magentoAttributesOptions' => $magentoAttributesOptions,
            'storeViewMapping'         => $this->getComputedStoreViewMapping(),
        );

        $this->globalContext['rootCategoryMapping'] = $this->getComputedRootCategoryMapping();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'enabled' => array(
                    'type'    => 'switch',
                    'options' => array(
                        'required' => true
                    )
                ),
                'visibility' => array(
                    'type'    => 'text',
                    'options' => array(
                        'required' => true
                    )
                ),
                'currency' => array(
                    'type'    => 'text',
                    'options' => array(
                        'required' => true
                    )
                ),
                'rootCategoryMapping' => array(
                    'type'    => 'textarea',
                    'options' => array(
                        'required' => false
                    )
                )
            )
        );
    }
}
