<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoWebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;

class HasValidCredentialsValidator extends ConstraintValidator
{
    /**
     * @var MagentoWebserviceGuesser
     */
    protected $magentoWebserviceGuesser;

    /**
     * @var IsValidWsdlUrlValidator
     */
    protected $isValidWsdlUrlValidator;

    /**
     * @param MagentoWebserviceGuesser $magentoWebserviceGuesser
     * @param IsValidWsdlUrlValidator  $isValidWsdlUrlValidator
     */
    public function __construct(
        MagentoWebserviceGuesser $magentoWebserviceGuesser,
        IsValidWsdlUrlValidator $isValidWsdlUrlValidator
    ) {
        $this->magentoWebserviceGuesser = $magentoWebserviceGuesser;
        $this->isValidWsdlUrlValidator  = $isValidWsdlUrlValidator;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param AbstractConfigurableStepElement $value      The value that should be validated
     * @param Constraint                      $constraint The constraint for the validation
     *
     * @api
     */
    public function validate($protocol, Constraint $constraint)
    {
        $clientParameters = new MagentoSoapClientParameters(
            $protocol->getSoapUsername(),
            $protocol->getSoapApiKey(),
            $protocol->getSoapUrl()
        );

        if ($this->isValidWsdlUrlValidator->isValidWsdlUrl($protocol->getSoapUrl())) {
            try {
                $this->magentoWebserviceGuesser->getWebservice($clientParameters);
            } catch (InvalidCredentialException $e) {
                $this->context->addViolation($constraint->message, array('soapUsername', 'soapApiKey'));
            }
        }
    }
}
