<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;


/**
 * Magento product writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoWriter extends AbstractConfigurableStepElement implements ItemWriterInterface
{
    /**
     * @Assert\NotBlank
     */
    protected $username;

    /**
     * @Assert\NotBlank
     */
    protected $apiKey;
    
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
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        print_r($items, true);

        return null;
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
        );
    }
}
