<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Factory\MagentoSoapClientFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\CurlException;
use Guzzle\Service\ClientInterface;

/**
 * This validator allows to validate if Magento is reachable with parameters of MagentoConfiguration entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoReachableValidator extends ConstraintValidator
{
    /** @staticvar int */
    const TIMEOUT = 10;

    /** @staticvar int */
    const CONNECT_TIMEOUT = 10;

    /** @staticvar string */
    const ACCESS_DENIED_CODE = '2';

    /** @var ClientInterface Guzzle HTTP client */
    protected $guzzleClient;

    /** @var MagentoSoapClientFactory Factory to create Soap clients */
    protected $soapClientFactory;

    /**
     * Constructor
     *
     * @param ClientInterface          $guzzleClient
     * @param MagentoSoapClientFactory $soapClientFactory
     */
    public function __construct(ClientInterface $guzzleClient, MagentoSoapClientFactory $soapClientFactory)
    {
        $this->guzzleClient      = $guzzleClient;
        $this->soapClientFactory = $soapClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($configuration, Constraint $constraint)
    {
        $response = $this->checkHttp($configuration, $constraint);

        if (null !== $response) {
           $this->checkWsdl($constraint, $response) && $this->checkSoap($configuration, $constraint);
        }
    }

    /**
     * Allows to check connection to the given SOAP URL
     *
     * @param MagentoConfiguration $configuration
     * @param Constraint           $constraint
     *
     * @return string|null $response
     */
    protected function checkHttp(MagentoConfiguration $configuration, Constraint $constraint)
    {
        try {
            $response  = $this->connectHttpClient($configuration);
        } catch (CurlException $e) {
            $this->context->addViolationAt('MagentoConfiguration', $constraint->messageNotReachableUrl);
            $response = null;
        } catch (BadResponseException $e) {
            $this->context->addViolationAt('MagentoConfiguration', $constraint->messageInvalidSoapUrl);
            $response = null;
        }

        return $response;
    }

    /**
     * It connects to the url and give response
     *
     * @param MagentoConfiguration $configuration
     *
     * @return array|\Guzzle\Http\Message\Response
     */
    protected function connectHttpClient(MagentoConfiguration $configuration)
    {
        $guzzleParams = [
            'connect_timeout' => static::CONNECT_TIMEOUT,
            'timeout'         => static::TIMEOUT,
            'auth'            => [
                $configuration->getHttpLogin(),
                $configuration->getHttpPassword()
            ]
        ];

        $request = $this->guzzleClient->get($configuration->getSoapUrl(), [], $guzzleParams);
        $request->getCurlOptions()->set(CURLOPT_CONNECTTIMEOUT, static::CONNECT_TIMEOUT);
        $request->getCurlOptions()->set(CURLOPT_TIMEOUT, static::TIMEOUT);

        $response = $this->guzzleClient->send($request);

        return $response;
    }

    /**
     * Check if response content type from WSDL URL is text/xml
     *
     * @param MagentoConfiguration $configuration
     * @param string               $response
     *
     * @return bool
     */
    protected function checkWsdl(Constraint $constraint, $response)
    {
        $isCorrect = true;
        if (false === $response->isContentType('text/xml')) {
            $this->context->addViolationAt('MagentoConfiguration', $constraint->messageXmlNotValid);
            $isCorrect = false;
        }

        return $isCorrect;
    }

    /**
     * Allows to check soap connection, login and api method
     *
     * @param MagentoConfiguration $configuration
     * @param Constraint           $constraint
     */
    protected function checkSoap(MagentoConfiguration $configuration, Constraint $constraint)
    {
        $magentoSoapClient = $this->soapClientFactory->createMagentoSoapClient($configuration);
        if (null !== $magentoSoapClient) {
            $this->loginSoapClient($magentoSoapClient, $constraint, $configuration);
        }
    }

    /**
     * Login Magento Soap client to verify if username and api key are rights and returns session token
     *
     * @param MagentoSoapClientInterface $soapClient
     * @param Constraint                 $constraint
     * @param MagentoConfiguration       $configuration
     *
     * @return string|null Session token
     */
    protected function loginSoapClient(
        MagentoSoapClientInterface $soapClient,
        Constraint $constraint,
        MagentoConfiguration $configuration
    ) {
        try {
            $session = $soapClient->login($configuration->getSoapUsername(), $configuration->getSoapApiKey());
        } catch (\SoapFault $e) {
            if (static::ACCESS_DENIED_CODE === $e->faultcode) {
                $this->context->addViolationAt('MagentoConfiguration', $constraint->messageAccessDenied);
            } else {
                $this->context->addViolationAt(
                    'MagentoConfiguration',
                    $constraint->messageUnmanagedSoapException,
                    [],
                    null,
                    null,
                    $e->faultcode
                );
            }
            $session = null;
        }

        return $session;
    }
}
