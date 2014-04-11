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
        } catch (InvalidMagentoUrlException $e) {
            $this->context->addViolation($constraint->messageUrlNotValid, array('%string%' => $value));
        } catch (InvalidXmlException $e) {
            $this->context->addViolation($constraint->messageXmlNotValid, array('%string%' => $value));
        }
    }

    /**
     * Test if the given base url leads to a valid wsdl url
     *
     * @param string $url The given url
     *
     * @return boolean
     */
    public function isValidMagentoUrl($url)
    {
        $result = true;
        try {
            $this->checkValidMagentoUrl($url);
        } catch (InvalidMagentoUrlException $e) {
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
    protected function checkValidMagentoUrl($url)
    {
        $output = $this->curlCall($url);

        if (false === $output) {
            throw new InvalidMagentoUrlException();
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
