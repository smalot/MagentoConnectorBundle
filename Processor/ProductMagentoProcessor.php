<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\ImportExportBundle\Converter\MetricConverter;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoWebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoNormalizerGuesser;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\NormalizeException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\IsValidWsdlUrl;

/**
 * Magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ProductMagentoProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface
{
    const MAGENTO_VISIBILITY_CATALOG_SEARCH = 4;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var metricConverter
     */
    protected $metricConverter;

    /**
     * @var MagentoWebservice
     */
    protected $magentoWebservice;

    /**
     * @var MagentoWebserviceGuesser
     */
    protected $magentoWebserviceGuesser;

    /**
     * @var MagentoNormalizerGuesser
     */
    protected $magentoNormalizerGuesser;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $soapUsername;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $soapApiKey;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @Assert\Url(groups={"Execution"})
     * @IsValidWsdlUrl(groups={"Execution"})
     */
    protected $soapUrl;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $channel;

    /**
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
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $defaultLocale;

    /**
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $website = 'base';

    /**
     * @var string
     */
    protected $storeViewMapping = '';

    /**
     * @var MagentoSoapClientParameters
     */
    protected $clientParameters;

    /**
     * @param ChannelManager           $channelManager
     * @param MagentoWebserviceGuesser $magentoWebserviceGuesser
     * @param ProductNormalizerGuesser $productNormalizerGuesser
     * @param MetricConverter          $metricConverter
     */
    public function __construct(
        ChannelManager           $channelManager,
        MagentoWebserviceGuesser $magentoWebserviceGuesser,
        MagentoNormalizerGuesser $magentoNormalizerGuesser,
        MetricConverter          $metricConverter
    ) {
        $this->channelManager           = $channelManager;
        $this->magentoWebserviceGuesser = $magentoWebserviceGuesser;
        $this->magentoNormalizerGuesser = $magentoNormalizerGuesser;
        $this->metricConverter          = $metricConverter;
    }

    /**
     * get soapUsername
     *
     * @return string Soap mangeto soapUsername
     */
    public function getSoapUsername()
    {
        return $this->soapUsername;
    }

    /**
     * Set soapUsername
     *
     * @param string $soapUsername Soap mangeto soapUsername
     */
    public function setSoapUsername($soapUsername)
    {
        $this->soapUsername = $soapUsername;

        return $this;
    }

    /**
     * get soapApiKey
     *
     * @return string Soap mangeto soapApiKey
     */
    public function getSoapApiKey()
    {
        return $this->soapApiKey;
    }

    /**
     * Set soapApiKey
     *
     * @param string $soapApiKey Soap mangeto soapApiKey
     */
    public function setSoapApiKey($soapApiKey)
    {
        $this->soapApiKey = $soapApiKey;

        return $this;
    }

    /**
     * get soapUrl
     *
     * @return string mangeto soap url
     */
    public function getSoapUrl()
    {
        return $this->soapUrl;
    }

    /**
     * Set soapUrl
     *
     * @param string $soapUrl mangeto soap url
     */
    public function setSoapUrl($soapUrl)
    {
        $this->soapUrl = $soapUrl;

        return $this;
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
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * get defaultLocale
     *
     * @return string defaultLocale
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }

    /**
     * Set defaultLocale
     *
     * @param string $defaultLocale defaultLocale
     */
    public function setDefaultLocale($defaultLocale)
    {
        $this->defaultLocale = $defaultLocale;

        return $this;
    }

    /**
     * get website
     *
     * @return string website
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set website
     *
     * @param string $website website
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * get storeViewMapping
     *
     * @return string storeViewMapping
     */
    public function getStoreViewMapping()
    {
        return $this->storeViewMapping;
    }

    /**
     * Set storeViewMapping
     *
     * @param string $storeViewMapping storeViewMapping
     */
    public function setStoreViewMapping($storeViewMapping)
    {
        $this->storeViewMapping = $storeViewMapping;

        return $this;
    }

    /**
     * Get computed storeView mapping (string to array)
     * @return array
     */
    protected function getComputedStoreViewMapping()
    {
        $computedStoreViewMapping = array();

        foreach (explode(chr(10), $this->storeViewMapping) as $line) {
            $computedStoreViewMapping[] = explode(':', $line);
        }

        return $computedStoreViewMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $this->magentoWebservice = $this->magentoWebserviceGuesser->getWebservice($this->getClientParameters());
        $this->productNormalizer = $this->magentoNormalizerGuesser->getNormalizer($this->getClientParameters());

        $processedItems = array();

        $magentoProducts          = $this->magentoWebservice->getProductsStatus($items);
        $magentoStoreViews        = $this->magentoWebservice->getStoreViewsList();
        $magentoAttributesOptions = $this->magentoWebservice->getAllAttributesOptions();

        $context = array(
            'magentoStoreViews'        => $magentoStoreViews,
            'magentoAttributesOptions' => $magentoAttributesOptions,
            'defaultLocale'            => $this->defaultLocale,
            'channel'                  => $this->channel,
            'website'                  => $this->website,
            'enabled'                  => $this->enabled,
            'visibility'               => $this->visibility,
            'magentoAttributes'        => $this->magentoWebservice->getAllAttributes(),
            'currency'                 => $this->currency,
            'storeViewMapping'         => $this->getComputedStoreViewMapping()
        );

        $this->metricConverter->convert($items, $this->channelManager->getChannelByCode($this->channel));

        foreach ($items as $product) {
            $context['attributeSetId'] = $this->getAttributeSetId($product);

            if ($this->magentoProductExist($product, $magentoProducts)) {
                if ($this->attributeSetChanged($product, $magentoProducts)) {
                    throw new InvalidItemException('The product family has changed of this product. This modification '.
                        'cannot be applied to magento. In order to change the family of this product, please manualy ' .
                        'delete this product in magento and re-run this connector.', array($product));
                }

                $context['create'] = false;
            } else {
                $context['create'] = true;
            }

            $processedItems[] = $this->normalizeProduct($product, $context);
        }

        return $processedItems;
    }

    /**
     * Normalize the given product
     *
     * @param  Product $product [description]
     * @param  array   $context The context
     * @return array processed item
     */
    protected function normalizeProduct(Product $product, $context)
    {
        try {
            $processedItem = $this->productNormalizer->normalize($product, 'MagentoArray', $context);
        } catch (NormalizeException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        }

        return $processedItem;
    }

    /**
     * Test if a product allready exist on magento platform
     *
     * @param  Product $product         The product
     * @param  array   $magentoProducts Magento products
     * @return bool
     */
    protected function magentoProductExist(Product $product, $magentoProducts)
    {
        foreach ($magentoProducts as $magentoProduct) {

            if ($magentoProduct['sku'] == $product->getIdentifier()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Test if the product attribute set changed
     *
     * @param  Product $product         The product
     * @param  array   $magentoProducts Magento products
     * @return bool
     */
    protected function attributeSetChanged(Product $product, $magentoProducts)
    {
        foreach ($magentoProducts as $magentoProduct) {
            if (
                $magentoProduct['sku'] == $product->getIdentifier() &&
                $magentoProduct['set'] != $this->getAttributeSetId($product)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the magento soap client parameters
     *
     * @return MagentoSoapClientParameters
     */
    protected function getClientParameters()
    {
        if (!$this->clientParameters) {
            $this->clientParameters = new MagentoSoapClientParameters(
                $this->soapUsername,
                $this->soapApiKey,
                $this->soapUrl
            );
        }

        return $this->clientParameters;
    }

    /**
     * Get the attribute set id for the given product
     *
     * @param  Product $product The product
     * @return integer
     */
    protected function getAttributeSetId(Product $product)
    {
        try {
            return $this->magentoWebservice
                ->getAttributeSetId(
                    $product->getFamily()->getCode()
                );
        } catch (AttributeSetNotFoundException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'soapUsername' => array(
                'options' => array(
                    'required' => true
                )
            ),
            'soapApiKey'   => array(
                //Should be remplaced by a password formType but who doesn't
                //empty the field at each edit
                'type'    => 'text',
                'options' => array(
                    'required' => true
                )
            ),
            'soapUrl' => array(
                'options' => array(
                    'required' => true
                )
            ),
            'channel' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true
                )
            ),
            'defaultLocale' => array(
                //Should be fixed to display only active locale on the selected
                //channel
                'type'    => 'text',
                'options' => array(
                    'required' => true
                )
            ),
            'website' => array(
                'type'    => 'text',
                'options' => array(
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
                'type'    => 'text',
                'options' => array(
                    'required' => true
                )
            ),
            'storeViewMapping' => array(
                'type'    => 'textarea',
                'options' => array(
                    'required' => false
                )
            )
        );
    }
}
