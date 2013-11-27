<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Pim\Bundle\CatalogBundle\Entity\Product;

use Pim\Bundle\MagentoConnectorBundle\Webservice\AttributeSetNotFoundException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;

/**
 * Magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface
{
    const MAGENTO_SIMPLE_PRODUCT_KEY = 'simple';

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var MagentoSoapClient
     */
    protected $magentoSoapClient;

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
     */
    protected $soapUrl;

    /**
     * @Assert\NotBlank
     */
    protected $channel;

    /**
     * @Assert\NotBlank
     */
    protected $defaultLocale;

    protected $clientParameters;
    protected $pimLocales;

    /**
     * @param ChannelManager $channelManager
     * @param MagentoSoapClient $channelManager
     */
    public function __construct(
        ChannelManager $channelManager,
        MagentoSoapClient $magentoSoapClient
    ) {
        $this->channelManager    = $channelManager;
        $this->magentoSoapClient = $magentoSoapClient;
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
     * {@inheritdoc}
     */
    public function process($items)
    {
        //Soap init
        $this->magentoSoapClient->init($this->getClientParameters());

        $processedItems = array();

        foreach ($items as $product) {
            $sku               = (string) $product->getIdentifier();
            $attributeSetId    = $this->getAttributeSetId($product);
            $defaultValues     = $this->getValues($product, $this->defaultLocale, $this->channel, false);
            $magentoStoreViews = $this->magentoSoapClient->getStoreViewsList();

            print_r($magentoStoreViews);

            $processedItem = array();

            //For the default storeview we create an entire product
            $processedItem[MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW] = array(
                self::MAGENTO_SIMPLE_PRODUCT_KEY,
                $attributeSetId,
                $sku,
                $defaultValues,
                MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW
            );

            //For each storeview, we create a version of the product only with localized attributes
            foreach ($magentoStoreViews as $magentoStoreView) {
                $storeViewCode = $magentoStoreView['code'];

                $locale = $this->getAkeneoLocaleForStoreView($storeViewCode);

                //If a locale for this storeview exist in akeneo, we create a translated product in this locale
                if ($locale) {
                    $values = $this->getValues($product, $locale, $this->channel, true);

                    $processedItem[$storeViewCode] = array(
                        $sku,
                        $values,
                        $storeViewCode
                    );
                }
            }

            $processedItems[] = $processedItem;
        }

        return $processedItems;
    }

    /**
     * Get the attribute set id for the given product
     *
     * @param  Product $product The product
     * @return integer
     */
    private function getAttributeSetId(Product $product)
    {
        try {
            return $this->magentoSoapClient
                ->getAttributeSetId(
                    $product->getFamily()->getCode(),
                    $this->getClientParameters()
                );
        } catch (AttributeSetNotFoundException $e) {
            throw new InvalidItemException($e->getMessage(), array($product));
        }
    }

    private function getClientParameters()
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
     * Get the corresponding akeneo locale for a given storeview code
     *
     * @param  string $storeViewCode The store view code
     * @return Locale The corresponding locale
     */
    private function getAkeneoLocaleForStoreView($storeViewCode)
    {
        $pimLocales = $this->getPimLocales();
        foreach ($pimLocales as $locale) {
            if (strtolower($locale->getCode()) == $storeViewCode) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Get all akeneo locales for the current channel
     * @return array The locales
     */
    private function getPimLocales()
    {
        if (!$this->pimLocales) {
            $this->pimLocales = $this->channelManager
                ->getChannels(array('code' => $this->channel))
                [0]
                ->getLocales();
        }

        return $this->pimLocales;
    }

    /**
     * Get values array for a given product
     *
     * @param  Product $product       The given product
     * @param  string  $locale        The locale to apply
     * @param  string  $scope         The akeno scope
     * @param  boolean $onlyLocalized If true, only get translatable attributes
     *
     * @return array Computed data
     */
    private function getValues(Product $product, $locale, $scope, $onlyLocalized = false)
    {
        $values = array();

        $pimAttributes     = $product->getAllAttributes();
        $magentoAttributes = $this->magentoSoapClient->getAttributeList($product->getFamily()->getCode());

        foreach ($magentoAttributes as $magentoAttribute) {
            if (($value = $this->getPimValue(
                $pimAttributes,
                $magentoAttribute,
                $product,
                $locale,
                $scope,
                $onlyLocalized
            )) !== null) {
                $values[$magentoAttribute['code']] = $value;
            }
        }

        return $values;
    }

    /**
     * Get the value the given attribute for the given product
     * @param  array  $pimAttributes Akeneo attribute list for the product
     * @param  array  $magentoAttribute Magento attribute list
     * @param  Product $product          The product
     * @param  string  $locale           The locale to apply
     * @param  string  $scope            The scope to apply
     * @param  boolean  $onlyLocalized   If true on the attribute is not translatable get a null for the value
     * @return mixed The formated value
     */
    private function getPimValue(
        $pimAttributes,
        $magentoAttribute,
        Product $product,
        $locale,
        $scope,
        $onlyLocalized
    ) {
        $attributesOptions    = $this->getAttributesOptions();
        $magentoAttributeCode = $magentoAttribute['code'];;

        $value = null;

        if (isset($attributesOptions[$magentoAttributeCode])) {

            $attributeOptions = $attributesOptions[$magentoAttributeCode];

            if (isset($attributeOptions['method'])) {
                $value = $this->getValueFromMethod($product, $attributeOptions);
            } else {
                $value = $this->getValueFromPimAttribute(
                    $product,
                    $attributeOptions,
                    $pimAttributes,
                    $magentoAttributeCode,
                    $locale,
                    $scope
                );
            }

            if ($onlyLocalized && !$attributeOptions['translatable']) {
                $value = null;
            }

            $value = $this->castValue($value, $attributeOptions);
        }

        return $value;
    }

    /**
     * Call the method from $attributeOptions and return the computed value
     *
     * @param  Product $product          The concerned product
     * @param  array   $attributeOptions Attribute options
     *
     * @return mixed The computed value
     */
    private function getValueFromMethod(Product $product, $attributeOptions)
    {
        $parameters = isset($attributeOptions['parameters']) ? $attributeOptions['parameters'] : array();
        $method     = $attributeOptions['method'];

        if (is_callable($method)) {
            $value = $method($product, $parameters);
        } else {
            $value = call_user_func_array(array($product, $attributeOptions['method']), $parameters);
        }

        return $value;
    }

    /**
     * Getting the value from the akeneo attribute
     *
     * @param  Product $product              The concerned product
     * @param  array   $attributeOptions     Attribute options
     * @param  array   $pimAttributes        Pim attributes
     * @param  string  $magentoAttributeCode The magento attribute code
     * @param  string  $locale               The locale to apply
     * @param  string  $scope                The scope to apply
     *
     * @return mixed
     */
    private function getValueFromPimAttribute(
        Product $product,
        $attributeOptions,
        $pimAttributes,
        $magentoAttributeCode,
        $locale,
        $scope
    ) {
        //If there is a mapping between magento and the pim
        if (isset($attributeOptions['mapping'])) {
            $pimAttribute  = $pimAttributes[$attributeOptions['mapping']];
        } else {
            $pimAttribute  = $pimAttributes[$magentoAttributeCode];
        }

        $attributeCode   = $pimAttribute->getCode();
        $attributeLocale = ($pimAttribute->getTranslatable()) ? $locale : null;
        $attributeScope  = ($pimAttribute->getScopable())     ? $scope  : null;

        return $product->getValue($attributeCode, $attributeLocale, $attributeScope);
    }

    /**
     * Cast the given value
     *
     * @param  mixed $value            Our value
     * @param  array $attributeOptions The attribute options
     *
     * @return mixed
     */
    private function castValue($value, $attributeOptions)
    {
        if ($value !== null && isset($attributeOptions['type'])) {
            $castMethod = $this->getCastOptions()[$attributeOptions['type']];

            $value = $castMethod($value);
        }

        return $value;
    }

    /**
     * Get cast options
     *
     * @return array
     */
    private function getCastOptions()
    {
        return array(
            'int' => function($value) {
                return (int) $value;
            },
            'string' => function($value) {
                return (string) $value;
            },
            'bool' => function($value) {
                return (int) $value;
            },
            'float' => function($value) {
                return (float) $value;
            },
            'date' => function($value) {
                return (string) $value->format(\DateTime::ATOM);
            },
        );
    }

    /**
     * Getting the attributes options
     *
     * @return array
     */
    private function getAttributesOptions()
    {
        return array(
            'name' => array(
                'translatable' => true,
                'type'         => 'string',
            ),
            'description' => array(
                'translatable' => true,
                'type'         => 'string',
                'mapping'      => 'long_description',
            ),
            'short_description' => array(
                'translatable' => true,
                'type'         => 'string',
            ),
            'status' => array(
                'translatable' => false,
                'type'         => 'bool',
                'method'       => 'isEnabled',
            ),
            'visibility' => array(
                'translatable' => false,
                'type'         => 'bool',
                'method'       => 'isEnabled',
            ),
            'created_at' => array(
                'translatable' => false,
                'type'         => 'date',
                'method'       => 'getCreated',
            ),
            'updated_at' => array(
                'translatable' => false,
                'type'         => 'date',
                'method'       => 'getUpdated',
            ),
            'price' => array(
                'translatable' => false,
                'type'         => 'float',
                'method'       => function($product, $params) {
                    return $product->getValue('price')->getPrices()->first()->getData();
                },
            ),
            'tax_class_id' => array(
                'translatable' => false,
                'type'         => 'int',
                'method'       => function ($product, $params) { return 0; }
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'soapUsername' => array(),
            'soapApiKey'   => array(
                //Should be remplaced by a password formType but who doesn't
                //empty the field at each edit
                'type' => 'text'
            ),
            'soapUrl' => array(),
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
                'type' => 'text'
            )
        );
    }
}
