<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidDefaultLocale;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCurrency;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\CurrencyManager;

/**
 * Abstract magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidDefaultLocale()
 * @HasValidCurrency()
 */
abstract class AbstractProductProcessor extends AbstractProcessor
{
    const MAGENTO_VISIBILITY_CATALOG_SEARCH = 4;

    /**
     * @var ProductNormalizer
     */
    protected $productNormalizer;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @var Currency
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $currency;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $channel;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var integer
     */
    protected $visibility = self::MAGENTO_VISIBILITY_CATALOG_SEARCH;

    /**
     * @var string
     */
    protected $categoryMapping = '';

    /**
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param LocaleManager            $localeManager
     * @param CurrencyManager          $currencyManager
     * @param ChannelManager           $channelManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager,
        ChannelManager $channelManager
    ) {
        parent::__construct($webserviceGuesser, $normalizerGuesser, $localeManager);

        $this->currencyManager = $currencyManager;
        $this->channelManager  = $channelManager;
    }

    /**
     * get channel
     *
     * @return string channel
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Set channel
     *
     * @param string $channel channel
     *
     * @return AbstractProcessor
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

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
     * get categoryMapping
     *
     * @return string categoryMapping
     */
    public function getCategoryMapping()
    {
        return $this->categoryMapping;
    }

    /**
     * Set categoryMapping
     *
     * @param string $categoryMapping categoryMapping
     *
     * @return AbstractProcessor
     */
    public function setCategoryMapping($categoryMapping)
    {
        $this->categoryMapping = $categoryMapping;

        return $this;
    }

    /**
     * Get computed storeView mapping (string to array)
     * @return array
     */
    protected function getComputedCategoryMapping()
    {
        return $this->getComputedMapping($this->categoryMapping);
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

        $this->globalContext = array_merge(
            $this->globalContext,
            array(
                'channel'                  => $this->channel,
                'website'                  => $this->website,
                'magentoStoreViews'        => $magentoStoreViews,
                'magentoAttributes'        => $magentoAttributes,
                'magentoAttributesOptions' => $magentoAttributesOptions,
                'storeViewMapping'         => $this->getComputedStoreViewMapping(),
                'categoryMapping'          => $this->getComputedCategoryMapping()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'channel' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->channelManager->getChannelChoices(),
                        'required' => true
                    )
                ),
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
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->currencyManager->getCurrencyChoices(),
                        'required' => true,
                        'attr' => array(
                            'class' => 'select2'
                        )
                    )
                ),
                'categoryMapping' => array(
                    'type'    => 'textarea',
                    'options' => array(
                        'required' => false
                    )
                )
            )
        );
    }
}
