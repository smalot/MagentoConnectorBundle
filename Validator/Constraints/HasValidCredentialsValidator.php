<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\ConnectionErrorException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;

class HasValidCredentialsValidator extends ConstraintValidator
{
    /**
     * @var MagentoSoapClient
     */
    protected $magentoSoapClient;

    /**
     * @param MagentoSoapClient $magentoSoapClient
     */
    public function __construct(MagentoSoapClient $magentoSoapClient)
    {
        $this->magentoSoapClient = $magentoSoapClient;
    }

    public function validate($protocol, Constraint $constraint)
    {
        $clientParameters = new MagentoSoapClientParameters(
            $protocol->getSoapUsername(),
            $protocol->getSoapApiKey(),
            $protocol->getSoapUrl()
        );

        try {
            $this->magentoSoapClient->init($clientParameters);
        } catch (ConnectionErrorException $e) {
            $this->context->addViolation($constraint->message . ' url', array('soapUrl'));
        } catch (InvalidCredentialException $e) {
            $this->context->addViolation($constraint->message, array('soapUsername', 'soapApiKey'));
        }
    }
}