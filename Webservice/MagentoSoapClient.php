<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap client to abstract interaction with the php soap client
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoSoapClient
{
    const SOAP_WSDL_URL = '/api/soap/?wsdl';

    protected $session;

    protected $client;

    protected $calls;

    protected $clientParameters;

    function __construct(MagentoSoapClientParameters $clientParameters)
    {
        $this->clientParameters = $clientParameters;

        $wsdlUrl                = $this->clientParameters->getSoapUrl() . self::SOAP_WSDL_URL;
        $soapOptions            = array('encoding' => 'UTF-8', 'trace' => 1, 'exceptions' => true);

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

    /**
     * Initialize the soap client with the local informations
     *
     * @throws ConnectionErrorException   If the connection to the soap api fail
     * @throws InvalidCredentialException If given credentials are invalid
     */
    protected function connect()
    {
        if ($this->clientParameters) {
            try {
                $this->session = $this->client->login(
                    $this->clientParameters->getSoapUsername(),
                    $this->clientParameters->getSoapApiKey()
                );
            } catch (\Exception $e) {
                throw new InvalidCredentialException(
                    'The given credential are invalid or not allowed to ' .
                    'connect to the soap api.',
                    $e->getCode(),
                    $e
                );
            }
        } else {
            throw new ConnectionErrorException(
                'Invalid state : you need to call the init method first'
            );
        }
    }

    public function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Login to the Magento platform
     */
    public function login()
    {
        $this->session = $this->client->login(
            $this->clientParameters->getUsername(),
            $this->clientParameters->getApiKey()
        );
    }

    /**
     * Is th client connected
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
     * @param  string $resource
     * @param  array  $params
     * @return mixed
     */
    public function call($resource, $params = null)
    {
        if ($this->isConnected()) {
            return $this->client->call($this->session, $resource, $params);
        } else {
            throw new NotConnectedException();
        }
    }

    /**
     * Add a call to the soap call stack
     *
     * @param array   $call         A magento soap call
     * @param integer $maximumCalls Send calls envery maximumCalls
     */
    public function addCall(array $call, $maximumCalls = 0)
    {
        $this->calls[] = $call;

        if ($maximumCalls > 0 && (count($this->calls) % $maximumCalls) == 0) {
            $this->sendCalls();
        }
    }

    /**
     * Send pending calls to the magento soap api (with multiCall function)
     *
     * @return mixed The soap response
     */
    public function sendCalls()
    {
        if (count($this->calls) > 0) {
            if ($this->isConnected()) {
                $responses = $this->multiCall(
                    $this->session,
                    $this->calls
                );

                foreach ($responses as $response) {
                    $this->processSoapResponse($response);
                }
            } else {
                throw new NotConnectedException();
            }

            $this->calls = array();
        }
    }

    /**
     * Process the soap response
     *
     * @param  mixed $response The soap response-
     */
    public function processSoapResponse($response)
    {
        if (is_array($response)) {
            if (isset($response['isFault']) && $response['isFault'] == 1) {

            }
        } else {
            if ($response == 1) {

            }
        }
    }
}
