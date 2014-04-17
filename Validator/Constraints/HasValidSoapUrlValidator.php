<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\Exceptions\InvalidSoapUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\Exceptions\InvalidXmlException;

/**
 * Validator for SOAP URL
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasValidSoapUrlValidator extends ConstraintValidator
{
    /*
     * @var HasValidApiUrlValidator
     */
    protected $hasValidApiUrlValidator;

    /**
     * @var string
     */
    protected $magentoUrl;

    /**
     * @var string
     */
    protected $wsdlUrl;

    /**
     * @var string
     */
    protected $soapUrl;

    /**
     * @var boolean
     */
    protected $checkedSoapUrl;

    /**
     * @var boolean
     */
    protected $isValidSoapUrl;

    /**
     * @param HasValidApiUrlValidator $hasValidApiUrlValidator
     */
    public function __construct(HasValidApiUrlValidator $hasValidApiUrlValidator)
    {
        $this->hasValidApiUrlValidator  = $hasValidApiUrlValidator;
    }

    /**
     *{@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        $this->soapUrl    = $value->getSoapUrl();
        $this->magentoUrl = $value->getMagentoUrl();
        $this->wsdlUrl    = $value->getWsdlUrl();

        if ($this->hasValidApiUrlValidator->isValidApiUrl($this->magentoUrl, $this->wsdlUrl)) {
            try {
                $output = $this->checkValidSoapUrl($this->soapUrl);
                $this->checkValidXml($output);
            } catch (InvalidSoapUrlException $e) {
                $this->context->addViolationAt('wsdlUrl', $constraint->messageUrlNotValid);
            } catch (InvalidXmlException $e) {
                $this->context->addViolationAt('wsdlUrl', $constraint->messageXmlNotValid);
            }
        }
    }

    /**
     * Test if the given base url leads to a valid wsdl url
     *
     * @param string $url The given url
     *
     * @return boolean
     */
    public function isValidSoapUrl($url)
    {
        if (!$this->checkedSoapUrl) {
            $this->checkedSoapUrl = true;

            try {
                $this->checkValidSoapUrl($url);
                $this->isValidSoapUrl = true;
            } catch (InvalidSoapUrlException $e) {
                $this->isValidSoapUrl = false;
            }
        }


        return $this->isValidSoapUrl;
    }

    /**
     * Check if the given base url leads to a valid wsdl url
     *
     * @param string $url The given url
     *
     * @return $output
     *
     * @throws InvalidMagentoUrlException
     */
    protected function checkValidSoapUrl($url)
    {
        $output = $this->curlCall($url);

        if (false === $output) {
            throw new InvalidSoapUrlException();
        }

        return $output;
    }

    /**
     * Check if the given url return a valid xml through soap
     *
     * @param string $output from the curl call
     *
     * @return $xmlElement
     *
     * @throws InvalidXmlException
     */
    protected function checkValidXml($output)
    {
        $xmlElement = simplexml_load_string($output, 'SimpleXMLElement', LIBXML_NOERROR);

        if (false === $xmlElement) {
            throw new InvalidXmlException();
        }

        return $xmlElement;
    }

    /*
     * Curl call
     *
     * @param $url The given url
     *
     * @return $output
     */
    protected function curlCall($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }
}
