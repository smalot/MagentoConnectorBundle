<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * Magento Soap client
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClient extends \SoapClient implements MagentoSoapClientInterface
{
    /** @staticvar string */
    const DEFAULT_SOCKET_TIMEOUT_VALUE = '120';

    /** @staticvar string */
    const PRODUCT_EXPORT_METHOD = 'import.importEntities';

    /** @staticvar string */
    const CATALOG_PRODUCT_ENTITY_TYPE = 'catalog_product';

    /** @staticvar string */
    const APPEND_BEHAVIOR = 'append';

    /** @var string */
    protected $session;

    /**
     * Constructor
     *
     * @param array  $soapOptions
     * @param string $soapUrl
     */
    public function __construct(array $soapOptions, $soapUrl)
    {
        parent::__construct($soapUrl, $soapOptions);

        $this->setDefaultSocketTimeout();
    }

    /**
     * {@inheritdoc}
     */
    public function login($username, $soapApiKey)
    {
        $this->session = parent::login($username, $soapApiKey);

        return $this->session;
    }

    /**
     * Allows to export products
     *
     * @param array $products
     */
    public function exportProducts(array $products)
    {
        $params = [
            $products,
            static::CATALOG_PRODUCT_ENTITY_TYPE,
            static::APPEND_BEHAVIOR
        ];

        $this->call($this->getValidSession(), static::PRODUCT_EXPORT_METHOD, $params);
    }

    /**
     * Get valid session token. If session is not valid, it throws an exception
     *
     * @throws ClientNotLoggedException
     *
     * @return string
     */
    protected function getValidSession()
    {
        if (null === $this->session) {
            throw new ClientNotLoggedException();
        }

        return $this->session;
    }

    /**
     * Set the default socket timeout configuration option
     */
    protected function setDefaultSocketTimeout()
    {
        ini_set('default_socket_timeout', static::DEFAULT_SOCKET_TIMEOUT_VALUE);
    }
}
