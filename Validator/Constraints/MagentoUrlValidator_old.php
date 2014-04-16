<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for Magento url
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoUrlValidator extends ConstraintValidator
{
    /**
     *{@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        try {
            $output = $this->checkValidMagentoUrl($value);
            $this->checkValidXml($output);
        } catch (InvalidSoapUrlException $e) {
            $this->context->addViolation($constraint->messageUrlNotValid, array('%string%' => $value));
        } catch (InvalidXmlException $e) {
            $this->context->addViolation($constraint->messageXmlNotValid, array('%string%' => $value));
        }
    }

    /**
     * Check if the given magento url is valid
     * if the last character is '/' it's not valid
     *
     * @param string $url the given magento url
     *
     * @return boolean
     *
     * @throws invalidMagentoUrlException
     */
    public function checkValidMagentoUrl($url)
    {
        if ('/' === substr($url, 0, -1)){
            throw new InvalidMagentoUrlException();
        }

        return true;
    }

    /**
     * Check if the given wsdl url is valid
     * if the fist character isn't a '/' it's not valid
     *
     * @param string $wsdl
     *
     * @return boolean
     *
     * @throws invalidWsdlUrlException
     */
    public function checkValidWsdlUrl($wsdl)
    {
        if ('/' !== substr($wsdl, 0, 1) ){
            throw new InvalidWsdlUrlException();
        }

        return true;
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
        $result = true;
        try {
            $this->checkValidSoapUrl($url);
        } catch (InvalidSoapUrlException $e) {
            $result = false;
        }

        return $result;
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
