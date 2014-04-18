<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap client to handle connection with magento soap api
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClient
{
    protected $session;

    protected $client;

    protected $calls;

    protected $clientParameters;

    protected static $instance;

    /**
     * Create and init the soap client
     *
     * @param  MagentoSoapClientParameters $clientParameters
     * @throws ConnectionErrorException
     * @throws InvalidCredentialException
     */
    protected function __construct(MagentoSoapClientParameters $clientParameters)
    {
        $this->clientParameters = $clientParameters;
            $wsdlUrl     = $this->clientParameters->getSoapUrl();
            $soapOptions = array('encoding' => 'UTF-8', 'trace' => 1, 'exceptions' => true);
        try {
            $this->client = new \SoapClient($wsdlUrl, $soapOptions);
        } catch (\Exception $e) {
            throw new ConnectionErrorException(
                'The soap connection could not be established',
                $e->getCode(),
                $e
            );
        }
        $this->connect();
    }

    /*
     * Prevent the class to be cloned
     */
    protected function __clone()
    {
    }

    /**
     * Initialize the soap client with the local informations
     *
     * @throws InvalidCredentialException If given credentials are invalid
     */
    protected function connect()
    {
        try {
            $this->session = $this->client->login(
                $this->clientParameters->getSoapUsername(),
                $this->clientParameters->getSoapApiKey()
            );
        } catch (\Exception $e) {
            throw new InvalidCredentialException(
                $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Is the client connected ?
     *
     * @return boolean
     */
    public function isConnected()
    {
        return (bool) $this->session;
    }

    /**
     * Call soap api
     *
     * @param string $resource
     * @param array $params
     *
     * @throws SoapCallException
     * @throws NotConnectedException
     * @return mixed
     */
    public function call($resource, $params = null)
    {
        if ($this->isConnected()) {
            try {
                $response = $this->client->call($this->session, $resource, $params);
            } catch (\SoapFault $e) {
                throw new SoapCallException(
                    sprintf(
                        'Error on Magento soap call : "%s". Called resource : "%s" with parameters : %s',
                        $e->getMessage(),
                        $resource,
                        json_encode($params)
                    )
                );
            }

            if (is_array($response) && isset($response['isFault']) && $response['isFault']) {
                throw new SoapCallException(
                    sprintf(
                        'Error on Magento soap call : "%s". Called resource : "%s" with parameters : %s.' .
                        'Response from API : %s',
                        $e->getMessage(),
                        $resource,
                        json_encode($params),
                        json_encode($response)
                    )
                );
            }

            return $response;
        } else {
            throw new NotConnectedException();
        }
    }

    /**
     * Add a call to the soap call stack
     *
     * @param array $call A magento soap call
     */
    public function addCall(array $call)
    {
        $this->call($call[0], $call[1]);
    }

    /**
     * Send pending calls to the magento soap api (with multiCall function)
     */
    public function sendCalls()
    {
        if (count($this->calls) > 0) {
            if ($this->isConnected()) {
                try {
                    $this->client->multiCall(
                        $this->session,
                        $this->calls
                    );
                } catch (\SoapFault $e) {
                    throw new SoapCallException(
                        sprintf(
                            'Error on Magento soap call : "%s". Called resources : "%s".',
                            $e->getMessage(),
                            json_encode($this->calls)
                        )
                    );
                }
            } else {
                throw new NotConnectedException();
            }

            $this->calls = array();
        }
    }

    /*
     * Return the singleton class instance if it exists or create it and return it
     *
     * @param MagentoSoapClient $clientParameters The client parameters
     * @param \SoapClient       $soapClient       The SoapClient class
     * @return MagentoSoapClient
     */
    public static function getInstance(MagentoSoapClientParameters $clientParameters)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($clientParameters);
        }

        return self::$instance;
    }
}
