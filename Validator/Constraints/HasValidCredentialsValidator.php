<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;

/**
 * Validator for Magento credentials
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasValidCredentialsValidator extends ConstraintValidator
{
    /**
     * @var WebserviceGuesser
     */
    protected $webserviceGuesser;

    /**
     * @var MagentoUrlValidator
     */
    protected $magentoUrlValidator;

    /**
     * @param WebserviceGuesser   $webserviceGuesser
     * @param MagentoUrlValidator $magentoUrlValidator
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        MagentoUrlValidator $magentoUrlValidator
    ) {
        $this->webserviceGuesser = $webserviceGuesser;
        $this->magentoUrlValidator      = $magentoUrlValidator;
    }

    /**
     * Checks if the passed value is valid.
     *
     * @param AbstractConfigurableStepElement $protocol   The value that should be validated
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
                $this->webserviceGuesser->getWebservice($clientParameters);
            } catch (InvalidCredentialException $e) {
                $this->context->addViolationAt('soapUsername', $constraint->messageUsername);
                $this->context->addViolationAt('soapApiKey', $constraint->messageApikey);
            }
        }
    }
}
