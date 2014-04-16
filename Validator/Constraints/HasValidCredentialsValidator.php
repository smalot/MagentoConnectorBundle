<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;

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
     * @var HasValidSoapUrlValidator
     */
    protected $hasValidSoapUrlValidator;

    /**
     * @var HasValidApiUrlValidator
     */
    protected $hasValidApiUrlValidator;

    /**
     * @var boolean
     */
    protected $checked = false;

    /**
     * @var boolean
     */
    protected $valid = false;

    /**
     * @param WebserviceGuesser   $webserviceGuesser
     * @param HasValidSoapUrlValidator $hasValidSoapUrlValidator
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        HasValidSoapUrlValidator $hasValidSoapUrlValidator,
        HasValidApiUrlValidator $hasValidApiUrlValidator
    ) {
        $this->webserviceGuesser        = $webserviceGuesser;
        $this->hasValidSoapUrlValidator = $hasValidSoapUrlValidator;
        $this->hasValidApiUrlValidator  = $hasValidApiUrlValidator;
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
            $protocol->getMagentoUrl(),
            $protocol->getWsdlUrl()
        );

        if ($this->hasValidApiUrlValidator->isValidApiUrl($clientParameters->getMagentoUrl(), $clientParameters->getWsdlUrl())
                && $this->hasValidSoapUrlValidator->isValidSoapUrl($clientParameters->getSoapUrl())
                && !$this->areValidSoapCredentials($clientParameters)) {
            $this->context->addViolationAt('soapUsername', $constraint->messageUsername, array());
            $this->context->addViolationAt('soapApikey', $constraint->messageApikey, array());
        }
    }

    /**
     * Are the given parameters valid ?
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return boolean
     */
    public function areValidSoapCredentials(MagentoSoapClientParameters $clientParameters)
    {
        if (!$this->checked) {
            $this->checked = true;
            
            try {
                $this->webserviceGuesser->getWebservice($clientParameters);
                $this->valid = true;
            } catch (InvalidCredentialException $e) {
                $this->valid = false;
            } catch (SoapCallException $e) {
                $this->valid = false;
            }
        }

        return $this->valid;
    }
}
