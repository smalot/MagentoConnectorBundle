<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;

/**
 * Magento product writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface
{
    const MAXIMUM_CALLS       = 1;
    const CREATE_PRODUCT_SIZE = 5;

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

    protected $clientParameters;

    /**
     * @param ChannelManager $channelManager
     * @param MagentoSoapClient $channelManager
     */
    public function __construct(
        ChannelManager $channelManager,
        MagentoSoapClient $magentoSoapClient
    )
    {
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
     * {@inheritdoc}
     */
    public function write(array $products)
    {
        if (!$this->clientParameters) {
            $this->clientParameters = new MagentoSoapClientParameters(
                $this->soapUsername,
                $this->soapApiKey,
                $this->soapUrl
            );
        }

        $this->magentoSoapClient->init($this->clientParameters);

        //creation for each product in the admin storeView (with default locale)
        foreach($products as $batch) {
            foreach ($batch as $product) {
                $this->computeProduct($product);
            }
        }

        $this->magentoSoapClient->sendCalls($this->clientParameters);
    }

    /**
     * Compute an individual product and all his parts (translations)
     *
     * @param  array $product The product and his parts
     */
    private function computeProduct($product)
    {
        $this->pruneImages($product);

        foreach(array_keys($product) as $storeViewCode) {
            $this->createCall($product[$storeViewCode], $storeViewCode);
        }
    }

    /**
     * Create a call for the given product part
     *
     * @param  array  $productPart      A product part
     * @param  string $storeViewCode The storeview code
     */
    private function createCall($productPart, $storeViewCode)
    {
        if ($storeViewCode == MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW) {
            if (count($productPart) == self::CREATE_PRODUCT_SIZE) {
                $resource = MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_CREATE;
            } else {
                $resource = MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_UPDATE;
            }

            $this->magentoSoapClient->addCall(
                array(
                    $resource,
                    $productPart,
                ),
                self::MAXIMUM_CALLS
            );
        } elseif ($storeViewCode == MagentoSoapClient::IMAGES) {
            $this->sendImages($productPart);
        } else {
            $this->magentoSoapClient->addCall(
                array(
                    MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_UPDATE,
                    $productPart,
                ),
                self::MAXIMUM_CALLS
            );
        }
    }

    /**
     * Send all product images
     *
     * @param  array $imagesCall All images to send
     */
    protected function sendImages($imagesCall)
    {
        foreach ($imagesCall as $imageCall) {
            $this->magentoSoapClient->addCall(
                array(
                    MagentoSoapClient::SOAP_ACTION_PRODUCT_MEDIA_CREATE,
                    $imageCall
                ),
                self::MAXIMUM_CALLS
            );
        }
    }

    /**
     * Get the sku of the given normalized product
     *
     * @param  array $product
     * @return string
     */
    protected function getProductSku($product)
    {
        $defaultStoreviewProduct = $product[MagentoSoapClient::SOAP_DEFAULT_STORE_VIEW];

        if (count($defaultStoreviewProduct) == self::CREATE_PRODUCT_SIZE) {
            return (string) $defaultStoreviewProduct[2];
        } else {
            return (string) $defaultStoreviewProduct[0];
        }
    }

    /**
     * Clean old images on magento product
     *
     * @param  array $product
     */
    protected function pruneImages($product)
    {
        $sku = $this->getProductSku($product);
        $images = $this->magentoSoapClient->getImages($sku);

        foreach ($images as $image) {
            $this->magentoSoapClient->deleteImage($sku, $image['file']);
        }
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
            'soapUrl'      => array(),
            'channel'      => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true
                )
            ),
        );
    }
}
