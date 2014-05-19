<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\NormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidDefaultLocale;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCurrency;
use Pim\Bundle\MagentoConnectorBundle\Manager\LocaleManager;
use Pim\Bundle\MagentoConnectorBundle\Merger\MagentoMappingMerger;
use Pim\Bundle\MagentoConnectorBundle\Manager\CurrencyManager;

/**
 * Abstract magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidDefaultLocale(groups={"Execution"})
 * @HasValidCurrency(groups={"Execution"})
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
    protected $categoryMapping;

    /**
     * @var MagentoMappingMerger
     */
    protected $categoryMappingMerger;

    /**
     * @var string
     */
    protected $attributeCodeMapping;

    /**
     * @var MagentoMappingMerger
     */
    protected $attributeMappingMerger;

    /**
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
     * @param LocaleManager            $localeManager
     * @param MagentoMappingMerger     $storeViewMappingMerger
     * @param CurrencyManager          $currencyManager
     * @param ChannelManager           $channelManager
     * @param MagentoMappingMerger     $categoryMappingMerger
     * @param MagentoMappingMerger     $attributeMappingMerger
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        NormalizerGuesser $normalizerGuesser,
        LocaleManager $localeManager,
        MagentoMappingMerger $storeViewMappingMerger,
        CurrencyManager $currencyManager,
        ChannelManager $channelManager,
        MagentoMappingMerger $categoryMappingMerger,
        MagentoMappingMerger $attributeMappingMerger
    ) {
        parent::__construct($webserviceGuesser, $normalizerGuesser, $localeManager, $storeViewMappingMerger);

        $this->currencyManager        = $currencyManager;
        $this->channelManager         = $channelManager;
        $this->categoryMappingMerger  = $categoryMappingMerger;
        $this->attributeMappingMerger = $attributeMappingMerger;
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
        return json_encode($this->categoryMappingMerger->getMapping()->toArray());
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
        $this->categoryMappingMerger->setMapping(json_decode($categoryMapping, true));

        return $this;
    }

    /**
     * get attribute code mapping
     *
     * @return string attributeCodeMapping
     */
    public function getAttributeCodeMapping()
    {
        return json_encode($this->attributeMappingMerger->getMapping()->toArray());
    }

    /**
     * Set attribute code mapping
     *
     * @param string $attributeCodeMapping attributeCodeMapping
     *
     * @return AbstractProcessor
     */
    public function setAttributeCodeMapping($attributeCodeMapping)
    {
        $this->attributeMappingMerger->setMapping(json_decode($attributeCodeMapping, true));

        return $this;
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
            $this->currency
        );

        $magentoStoreViews        = $this->webservice->getStoreViewsList();
        $magentoAttributes        = $this->webservice->getAllAttributes();
        $magentoAttributesOptions = $this->webservice->getAllAttributesOptions();

        $this->globalContext = array_merge(
            $this->globalContext,
            array(
                'channel'                  => $this->channel,
                'website'                  => $this->website,
                'magentoAttributes'        => $magentoAttributes,
                'magentoAttributesOptions' => $magentoAttributesOptions,
                'magentoStoreViews'        => $magentoStoreViews,
                'categoryMapping'          => $this->categoryMappingMerger->getMapping(),
                'attributeCodeMapping'         => $this->attributeMappingMerger->getMapping()
            )
        );
    }

    /**
     * Called after the configuration is set
     */
    protected function afterConfigurationSet()
    {
        parent::afterConfigurationSet();

        $this->categoryMappingMerger->setParameters($this->getClientParameters(), $this->getDefaultStoreView());
        $this->attributeMappingMerger->setParameters($this->getClientParameters(), $this->getDefaultStoreView());
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
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.channel.help',
                        'label'    => 'pim_magento_connector.export.channel.label'
                    )
                ),
                'enabled' => array(
                    'type'    => 'switch',
                    'options' => array(
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.enabled.help',
                        'label'    => 'pim_magento_connector.export.enabled.label'
                    )
                ),
                'visibility' => array(
                    'type'    => 'text',
                    'options' => array(
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.visibility.help',
                        'label'    => 'pim_magento_connector.export.visibility.label'
                    )
                ),
                'currency' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => $this->currencyManager->getCurrencyChoices(),
                        'required' => true,
                        'attr' => array(
                            'class' => 'select2',
                            'help'     => 'pim_magento_connector.export.currency.help',
                            'label'    => 'pim_magento_connector.export.currency.label'
                        )
                    )
                )
            ),
            $this->categoryMappingMerger->getConfigurationField(),
            $this->attributeMappingMerger->getConfigurationField()
        );
    }
}
