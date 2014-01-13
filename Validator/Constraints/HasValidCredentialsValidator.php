<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoWebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;

class HasValidCredentialsValidator extends ConstraintValidator
{
    /**
     * @var MagentoWebserviceGuesser
     */
    protected $magentoWebserviceGuesser;

    /**
     * @var MagentoUrlValidator
     */
    protected $magentoUrlValidator;

    /**
     * @param MagentoWebserviceGuesser $magentoWebserviceGuesser
     * @param MagentoUrlValidator      $magentoUrlValidator
     */
    public function __construct(
        MagentoWebserviceGuesser $magentoWebserviceGuesser,
        MagentoUrlValidator $magentoUrlValidator
    ) {
        $this->magentoWebserviceGuesser = $magentoWebserviceGuesser;
        $this->magentoUrlValidator      = $magentoUrlValidator;
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

        if ($this->magentoUrlValidator->isValidMagentoUrl($protocol->getSoapUrl())) {
            try {
                $this->magentoWebserviceGuesser->getWebservice($clientParameters);
            } catch (InvalidCredentialException $e) {
                $this->context->addViolation($constraint->message, array('soapUsername', 'soapApiKey'));
            }
        }
    }
}
