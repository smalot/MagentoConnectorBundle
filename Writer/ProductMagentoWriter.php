<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;


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
    const SOAP_SUFFIX_URL = '/api/soap/?wsdl';

    const SOAP_ACTION_CATALOG_PRODUCT_CREATE        = 'catalog_product.create';
    const SOAP_ACTION_CATALOG_PRODUCT_UDAPTE        = 'catalog_product.udapte';
    const SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE = 
        'catalog_product.currentStore';

    /** 
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @Assert\NotBlank
     */
    protected $username;

    /**
     * @Assert\NotBlank
     */
    protected $apiKey;

    /**
     * @Assert\NotBlank
     */
    protected $soapUrl;

    /**
     * @Assert\NotBlank
     */
    protected $channel;

    protected $client;
    protected $session;
    
    /**
     * @param ChannelManager $channelManager
     */
    public function __construct(
        ChannelManager $channelManager
    )
    {
        $this->channelManager = $channelManager;
    }
    
    /**
     * get username
     * 
     * @return string Soap mangeto username
     */
    public function getUsername() 
    {
        return $this->username;
    }

    /**
     * Set username
     * 
     * @param string $username Soap mangeto username
     */
    public function setUsername($username) 
    {
        $this->username = $username;

        return $this;
    }

    /**
     * get apiKey
     * 
     * @return string Soap mangeto apiKey
     */
    public function getApiKey() 
    {
        return $this->apiKey;
    }

    /**
     * Set apiKey
     *
      * @param string $apiKey Soap mangeto apiKey
     */
    public function setApiKey($apiKey) 
    {
        $this->apiKey = $apiKey;

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
        // We create the soap client and its session for this writer instance
        if (!$this->client) {
            try {
                $this->client = new \SoapClient(
                    $this->soapUrl . self::SOAP_SUFFIX_URL,
                    array(
                        'encoding' => 'UTF-8',
                        'trace'    => true
                    )
                );
            } catch (\Exception $e) {
                print_r($e);

                return null;
            }
            
            try {
                $this->session = $this->client->login(
                    $this->username, 
                    $this->apiKey
                );
            } catch (\Exception $e) {
                print_r($e);

                return null;
            }
        }

        $this->client->call(
            $this->session, 
            self::SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE, 
            'admin'
        );

        $calls = array();

        //creation for each product in the admin storeView (with default locale)
        foreach ($items as $item) {
            $calls[] = array(
                self::SOAP_ACTION_CATALOG_PRODUCT_CREATE,
                $item['default'],
            );
        }

        $this->client->multiCall(
            $this->session, 
            $calls
        );

        $locales = $this->channel->getLocales();

        //Update of each products and for each locale in their respective 
        //storeViews
        
        foreach ($locales as $locale) {
            $this->client->call(
                $this->session, 
                self::SOAP_ACTION_CATALOG_PRODUCT_CURRENT_STORE, 
                strtolower($locale)
            );

            $calls = array();

            foreach ($items as $item) {
                $calls[] = array(
                    self::SOAP_ACTION_CATALOG_PRODUCT_UPDATE,
                    $item[$locale],
                );
            }

            $this->client->multiCall(
                $this->session, 
                $calls
            );
        }

        

        // $this->client->call(
        //     $this->session, 
        //     self::SOAP_ACTION_CATALOG_PRODUCT_CREATE, 
        //     $calls[0][1]
        // );

        print_r($this->client->__getLastResponse());
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'username' => array(),
            'apiKey'   => array(
                'type' => 'password'
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
