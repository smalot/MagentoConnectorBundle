<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Factory\SoapClientFactory;
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

    /** @var ClientInterface Guzzle HTTP client */
    protected $guzzleClient;

    /** @var SoapClientFactory Factory to create Soap clients */
    protected $soapClientFactory;

    /**
     * Constructor
     *
     * @param ClientInterface   $guzzleClient
     * @param SoapClientFactory $soapClientFactory
     */
    public function __construct(ClientInterface $guzzleClient, SoapClientFactory $soapClientFactory)
    {
        $this->guzzleClient      = $guzzleClient;
        $this->soapClientFactory = $soapClientFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($configuration, Constraint $constraint)
    {
        if ($this->checkHttp($configuration, $constraint)) {
            $this->checkSoap($configuration, $constraint);
        }
    }

    /**
     * Allows to check connection to the given soap URL
     *
     * @param MagentoConfiguration $configuration
     * @param Constraint           $constraint
     *
     * @return bool
     */
    protected function checkHttp(MagentoConfiguration $configuration, Constraint $constraint)
    {
        try {
            $response  = $this->connectHttpClient($configuration);
            $isConnected = true;
        } catch (CurlException $e) {
            // When you can not access to anything and it returns a 404
            $this->context->addViolationAt(
                'MagentoConfiguration',
                $constraint->messageNotReachableUrl,
                [$e->getMessage()],
                [
                    'soapUrl' => $configuration->getSoapUrl(),
                    'httpLogin' => $configuration->getHttpLogin(),
                    'httpPassword' => $configuration->getHttpPassword()
                ]
            );
            $isConnected = false;
        } catch (BadResponseException $e) {
            // When you can access to Magento but it returns a 404
            $this->context->addViolationAt(
                'MagentoConfiguration',
                $constraint->messageInvalidSoapUrl,
                [$e->getMessage()],
                $configuration->getSoapUrl()
            );
            $isConnected = false;
        }

        if ($isConnected && false === $response->isContentType('text/xml')) {
            // When the response is not XML
            $this->context->addViolationAt(
                'MagentoConfiguration',
                $constraint->messageXmlNotValid,
                ['Content type is not XML'],
                $configuration->getSoapUrl()
            );
            $isConnected = false;
        }

        return $isConnected;
    }

    /**
     * It connects to the url and give response
     *
     * @param MagentoConfiguration $configuration
     *
     * @return \Guzzle\Http\Message\Response|array
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
     * Allows to check soap connection, login and api method
     *
     * @param MagentoConfiguration $configuration
     * @param Constraint           $constraint
     */
    protected function checkSoap(MagentoConfiguration $configuration, Constraint $constraint)
    {
        $soapClient = $this->connectSoapClient($constraint, $configuration);
        if (null !== $soapClient) {
            $this->loginSoapClient($soapClient, $constraint, $configuration);
        }
    }

    /**
     * Connect soap client and verify if soap url is valid
     *
     * @param Constraint           $constraint
     * @param MagentoConfiguration $configuration
     *
     * @return \SoapClient|null
     */
    protected function connectSoapClient(Constraint $constraint, MagentoConfiguration $configuration)
    {
        try {
            $soapClient = $this->soapClientFactory->createSoapClient($configuration);
        } catch (\SoapFault $e) {
            if (false !== stripos($e->getMessage(), 'failed to load external entity')) {
                $this->context->addViolationAt(
                    'MagentoConfiguration',
                    $constraint->messageInvalidSoapUrl,
                    [$e->getMessage()],
                    $configuration->getSoapUrl()
                );
            } else {
                $this->addUndefinedViolation($e, $constraint, $configuration);
            }
        }

        return $soapClient;
    }

    /**
     * Login Soap client to verify if username and api key are corrected and returns session token
     *
     * @param \SoapClient          $soapClient
     * @param Constraint           $constraint
     * @param MagentoConfiguration $configuration
     *
     * @return string|null Session token
     */
    protected function loginSoapClient(
        \SoapClient $soapClient,
        Constraint $constraint,
        MagentoConfiguration $configuration
    ) {
        try {
            $session = $soapClient->login($configuration->getSoapUsername(), $configuration->getSoapApiKey());
        } catch (\SoapFault $e) {
            if (false !== stripos($e->getMessage(), 'access denied')) {
                $this->context->addViolationAt(
                    'MagentoConfiguration',
                    $constraint->messageAccessDenied,
                    [$e->getMessage()],
                    [
                        $configuration->getSoapUsername(),
                        $configuration->getSoapApiKey()
                    ]
                );
            } else {
                $this->addUndefinedViolation($e, $constraint, $configuration);
            }
        }

        return $session;
    }

    /**
     * Add a violation at MagentoConfiguration with the UndefinedSoapException message
     *
     * @param \Exception           $exception
     * @param Constraint           $constraint
     * @param MagentoConfiguration $configuration
     */
    protected function addUndefinedViolation(
        \Exception $exception,
        Constraint $constraint,
        MagentoConfiguration $configuration
    ) {
        $this->context->addViolationAt(
            'MagentoConfiguration',
            $constraint->messageUndefinedSoapException,
            [$exception->getMessage()],
            [
                'soapUsername' => $configuration->getSoapUsername(),
                'soapApiKey'   => $configuration->getSoapApiKey(),
                'soapUrl'      => $configuration->getSoapUrl(),
                'httpLogin'    => $configuration->getHttpLogin(),
                'httpPassword' => $configuration->getHttpPassword()
            ]
        );
    }
}
