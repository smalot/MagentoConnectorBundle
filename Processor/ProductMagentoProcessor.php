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
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductCreateNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductUpdateNormalizer;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\InvalidOptionException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\InvalidScopeMatchException;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\AttributeNotFoundException;
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
     * @var MagentoSoapClient
     */
    protected $magentoSoapClient;

    /**
     * @var ProductCreateNormalizer
     */
    protected $productCreateNormalizer;

    /**
     * @var ProductUpdateNormalizer
     */
    protected $productUpdateNormalizer;

    /**
     * @Assert\NotBlank
     */
    protected $soapUsername;

    /**
     * @Assert\NotBlank
     */
    protected $soapApiKey;

    /**
     * @Assert\NotBlank
     * @IsValidWsdlUrl()
     */
    protected $soapUrl;

    /**
     * @Assert\NotBlank
     */
    protected $channel;

    /**
     * @Assert\NotBlank
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
     * @Assert\NotBlank
     */
    protected $defaultLocale;

    /**
     * @Assert\NotBlank
     */
    protected $website = 'base';

    /**
     * @var MagentoSoapClientParameters
     */
    protected $clientParameters;

    /**
     * @param ChannelManager    $channelManager
     * @param MagentoSoapClient $magentoSoapClient
     * @param ProductCreateNormalizer $productCreateNormalizer
     * @param ProductUpdateNormalizer $productUpdateNormalizer
     * @param MetricConverter $metricConverter
     */
    public function __construct(
        ChannelManager $channelManager,
        MagentoSoapClient $magentoSoapClient,
        ProductCreateNormalizer $productCreateNormalizer,
        ProductUpdateNormalizer $productUpdateNormalizer,
        MetricConverter $metricConverter
    ) {
        $this->channelManager          = $channelManager;
        $this->magentoSoapClient       = $magentoSoapClient;
        $this->productCreateNormalizer = $productCreateNormalizer;
        $this->productUpdateNormalizer = $productUpdateNormalizer;
        $this->metricConverter         = $metricConverter;
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
     * {@inheritdoc}
     */
    public function process($items)
    {
        //Soap init
        $this->magentoSoapClient->init($this->getClientParameters());

        $processedItems = array();

        $magentoProducts          = $this->magentoSoapClient->getProductsStatus($items);
        $magentoStoreViews        = $this->magentoSoapClient->getStoreViewsList();
        $magentoAttributesOptions = $this->magentoSoapClient->getAllAttributesOptions();

        $context = array(
            'magentoStoreViews'        => $magentoStoreViews,
            'magentoAttributesOptions' => $magentoAttributesOptions,
            'defaultLocale'            => $this->defaultLocale,
            'channel'                  => $this->channel,
            'website'                  => $this->website,
            'enabled'                  => $this->enabled,
            'visibility'               => $this->visibility,
            'magentoAttributes'        => $this->magentoSoapClient->getAllAttributes(),
            'currency'                 => $this->currency
        );

        $this->metricConverter->convert($items, $this->channelManager->getChannelByCode($this->channel));

        foreach ($items as $product) {
            $context['attributeSetId']    = $this->getAttributeSetId($product);

            if ($this->magentoProductExist($product, $magentoProducts)) {
                if ($this->attributeSetChanged($product, $magentoProducts)) {
                    throw new InvalidItemException('The product family has changed of this product. This modification '.
                        'cannot be applied to magento. In order to change the family of this product, please manualy ' .
                        'delete this product in magento and re-run this connector.', array($product));
                }

                $processedItems[] = $this->normalizeProduct($product, $context, false);
            } else {
                $processedItems[] = $this->normalizeProduct($product, $context, true);
            }
        }

        return $processedItems;
    }

    /**
     * Normalize the given product
     *
     * @param  Product $product [description]
     * @param  array   $context The context
     * @param  boolean $create  Is it a product creation ?
     * @return array processed item
     */
    protected function normalizeProduct(Product $product, $context, $create)
    {
        try {
            if ($create) {
                $processedItem = $this->productCreateNormalizer->normalize($product, null, $context);
            } else {
                $processedItem = $this->productUpdateNormalizer->normalize($product, null, $context);
            }
        } catch (InvalidOptionException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        } catch(InvalidScopeMatchException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        } catch(AttributeNotFoundException $e) {
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
            if ($magentoProduct['sku'] == $product->getIdentifier()) {
                if ($magentoProduct['set'] != $this->getAttributeSetId($product)) {
                    return true;
                }
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
            return $this->magentoSoapClient
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
            )
        );
    }
}
