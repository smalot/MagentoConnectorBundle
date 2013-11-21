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
    public function write(array $items)
    {
        if (!$this->clientParameters) {
            $this->clientParameters = new MagentoSoapClientParameters(
                $this->soapUsername,
                $this->soapApiKey,
                $this->soapUrl
            );
        }

        $this->magentoSoapClient->init($this->clientParameters);

        $this->magentoSoapClient->setCurrentStoreView(
            'admin', 
            $this->clientParameters
        );

        $calls = array();

        //creation for each product in the admin storeView (with default locale)
        foreach ($items as $item) {
            $this->magentoSoapClient->addCall(
                array(
                    MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_CREATE,
                    $item['default'],
                ),
                $this->clientParameters
            );
        }

        $this->magentoSoapClient->sendCalls($this->clientParameters);

        //A locale -> storeView mapping will have to be done in configuration
        //later. For now we will asume that we have a viewStore in magento for 
        //each akeneo locales
        
        $locales = $this->channelManager
            ->getChannels(array('code' => $this->channel))
            [0]
            ->getLocales();

        //Update of each products and for each locale in their respective 
        //storeViews
        
        foreach ($locales as $locale) {
            $this->magentoSoapClient->setCurrentStoreView(
                strtolower($locale), 
                $this->clientParameters
            );

            $calls = array();

            foreach ($items as $item) {
                $this->magentoSoapClient->addCall(
                    array(
                        MagentoSoapClient::SOAP_ACTION_CATALOG_PRODUCT_UPDATE,
                        $item[$locale->getCode()],
                    ),
                    $this->clientParameters
                );
            }

            $this->magentoSoapClient->flush($this->clientParameters);
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
            'soapUrl' => array(),
            'channel' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true
                )
            ),
        );
    }
}
