<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\ConnectionErrorException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;
use Symfony\Component\Debug\Exception\FatalErrorException;

class HasValidCredentialsValidator extends ConstraintValidator
{
    /**
     * @var MagentoSoapClient
     */
    protected $magentoSoapClient;

    /**
     * @var IsValidWsdlUrlValidator
     */
    protected $isValidWsdlUrlValidator;

    /**
     * @param MagentoSoapClient       $magentoSoapClient
     * @param IsValidWsdlUrlValidator $isValidWsdlUrlValidator
     */
    public function __construct(MagentoSoapClient $magentoSoapClient, IsValidWsdlUrlValidator $isValidWsdlUrlValidator)
    {
        $this->magentoSoapClient       = $magentoSoapClient;
        $this->isValidWsdlUrlValidator = $isValidWsdlUrlValidator;
    }

    public function validate($protocol, Constraint $constraint)
    {
        $clientParameters = new MagentoSoapClientParameters(
            $protocol->getSoapUsername(),
            $protocol->getSoapApiKey(),
            $protocol->getSoapUrl()
        );

        if ($this->isValidWsdlUrlValidator->isValidWsdlUrl($protocol->getSoapUrl())) {
            try {
                $this->magentoSoapClient->init($clientParameters);
            } catch (InvalidCredentialException $e) {
                $this->context->addViolation($constraint->message, array('soapUsername', 'soapApiKey'));
            }
        }
    }
}