<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

use Pim\Bundle\MagentoConnectorBundle\Writer\ProductMagentoWriter;

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
    const SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST = 'product_attribute_set.list';

    const MAGENTO_SIMPLE_PRODUCT_KEY = 'simple';

    /** 
     * @var ChannelManager
     */
    protected $channelManager;

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

    protected $magentoAttributeSets;

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
    public function process($item)
    {
        // We create the soap client and its session for this writer instance
        if (!$this->client) {
            try {
                $this->client = new \SoapClient(
                    $this->soapUrl . ProductMagentoWriter::SOAP_SUFFIX_URL,
                    array(
                        'encoding' => 'UTF-8'
                    )
                );
            } catch (\Exception $e) {
                
                //We should create a proper exception
                throw $e;
            }
            
            try {
                $this->session = $this->client->login(
                    $this->soapUsername, 
                    $this->soapApiKey
                );
            } catch (\Exception $e) {
                //We should create a proper exception
                throw $e;
            }
        }

        // On first call we get the magento attribute set list 
        // (to bind them with our proctut's families)
        if (!$this->magentoAttributeSets) {
            $attributeSets = $this->client->call(
                $this->session, 
                self::SOAP_ACTION_PRODUCT_ATTRIBUTE_SET_LIST
            );

            foreach ($attributeSets as $attributeSet) {
                print_r($attributeSet);
                $this->magentoAttributeSets[$attributeSet['name']] =
                    $attributeSet['set_id'];
            }
        }

        //Should be fixed in BETA-3
        $item = $item[0];

        if (!isset(
            $this->magentoAttributeSets[$item->getFamily()->getCode()]
        )) {
            print_r(
                array(
                    'unknow attribute set',
                    $item->getFamily()->getCode(),
                    $item->getId()
                )
                
            );

            //We should create a proper exception
            throw new InvalidItemException('Magento attributeSet not found', array($item));
        }

        $prices = explode(',', (string) $item->getValue('price', null, null));
        $price = explode(' ', $prices[0]);


        $result = array(
            'default' => array(
                self::MAGENTO_SIMPLE_PRODUCT_KEY,
                $this->magentoAttributeSets[$item->getFamily()->getCode()],
                (string) $item->getIdentifier(),
                array(
                    'name'              => (string) $item->getValue(
                        'name', 
                        $this->defaultLocale, 
                        $this->channel
                    ),
                    'description'       => (string) $item->getValue(
                        'short_description', 
                        $this->defaultLocale, 
                        $this->channel
                    ),
                    'short_description' => (string) $item->getValue(
                        'short_description', 
                        $this->defaultLocale, 
                        $this->channel
                    ),
                    'weight'            => '10',
                    'status'            => (string) (int) $item->isEnabled(),
                    'visibility'        => '4',
                    'price'             => $price[0],
                    'tax_class_id'      => 0,
                )
            )
        );

        //A locale -> storeView mapping will have to be done in configuration
        //later. For now we will asume that we have a viewStore in magento for 
        //each akeneo locales
        
        $channel = $this->channelManager
            ->getChannels(array('code' => $this->channel));
        $locales = $channel[0]->getLocales();

        foreach ($locales as $locale) {
            $result[$locale->getCode()] = array(
                (string) $item->getIdentifier(),
                array(
                    'name'              => (string) $item->getValue(
                        'name', 
                        $locale, 
                        $this->channel
                    ),
                    'description'       => (string) $item->getValue(
                        'short_description', 
                        $locale, 
                        $this->channel
                    ),
                    'short_description' => (string) $item->getValue(
                        'short_description', 
                        $locale, 
                        $this->channel
                    )
                )
                
            );
        }

        return true;
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
