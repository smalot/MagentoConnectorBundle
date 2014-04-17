<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for API URL
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasValidApiUrlValidator extends ConstraintValidator
{
    /**
     * @var string
     */
    protected $magentoUrl;

    /**
     * @var string
     */
    protected $wsdlUrl;

    /**
     * @var boolean
     */
    protected $checkedApiUrl = false;

    /**
     * @var boolean
     */
    protected $checkedMagentoUrl = false;

    /**
     * @var boolean
     */
    protected $isValidApiUrl = false;

    /**
     * @var boolean
     */
    protected $isValidMagentoUrl = false;

    /**
     *{@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $this->magentoUrl = $value->getMagentoUrl();
        $this->wsdlUrl = $value->getWsdlUrl();

        if (!$this->isValidMagentoUrl($this->magentoUrl)) {
            $this->context->addViolationAt('magentoUrl', $constraint->messageMagentoUrl);
        } elseif (!$this->isValidApiUrl($this->magentoUrl, $this->wsdlUrl)) {
            $this->context->addViolationAt('magentoUrl', $constraint->messageApiUrl);
        }
    }

    /**
     * Test if the Api Url is valid
     *
     * @param $magentoUrl
     * @param $wsdlUrl
     *
     * @return boolean
     */
    public function isValidApiUrl($magentoUrl, $wsdlUrl)
    {
        if (!$this->checkedApiUrl) {
            $this->checkedApiUrl = true;

            if (!$this->isValidMagentoUrl($magentoUrl)) {
                $this->isValidApiUrl = false;
            // Verify if $magentoUrl doesn't end with a / and $wsdlUrl doesn't begin with a / together.
            } elseif (!$this->isWellFormedUrl($magentoUrl, $wsdlUrl)) {
                $this->isValidApiUrl = false;
            } else {
                $this->isValidApiUrl = true;
            }
        }

        return $this->isValidApiUrl;
    }

    /**
     * Check if the url is well formed
     *
     * @param $magentoUrl
     * @param $wsdlUrl
     *
     * @return boolean
     */
    public function isWellFormedUrl($magentoUrl, $wsdlUrl)
    {
        return ('/' === substr($magentoUrl, -1) xor '/' === substr($wsdlUrl, 0, 1));
    }

    /**
     * Test if the Magento URL return a 200 http status
     *
     * @param string $magentoUrl
     *
     * @return boolean $isValid
     */
    protected function isValidMagentoUrl($magentoUrl)
    {
        if (!$this->checkedMagentoUrl) {
            $this->checkedMagentoUrl = true;

            if (filter_var($magentoUrl, FILTER_VALIDATE_URL)) {
                $headers = @get_headers($magentoUrl);

                if (false === strpos($headers[0], '200')) {
                    $this->isValidMagentoUrl = false;
                } else {
                    $this->isValidMagentoUrl = true;
                }
            } else {
                $this->isValidMagentoUrl = false;
            }
        }

        return $this->isValidMagentoUrl;
    }
}
