<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;

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
            if (!$this->isValidMagentoUrl($value)) {
                throw new InvalidMagentoUrlException();
            }
            $output = $this->isValidXml($value);
            $this->isValidXMLObject($output);
        } catch (InvalidMagentoUrlException $e) {
            $this->context->addViolation($constraint->messageUrlNotValid, array('%string%' => $value));
        } catch (InvalidXmlException $e) {
            $this->context->addViolation($constraint->messageXmlNotValid, array('%string%' => $value));
        } catch (InvalidXmlObjectException $e) {
            $this->context->addViolation($constraint->messageXmlObjectNotValid, array('%string%' => $value));
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
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . MagentoSoapClient::SOAP_WSDL_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        if ($output) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Test if the given url return a valid xml throw soap
     *
     * @param string $url the given url
     *
     * @return Object SimpleXMLElement or throw an InvalidXmlException
     */
    public function isValidXml($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . MagentoSoapClient::SOAP_WSDL_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FAILONERROR, 1);
        $xml = curl_exec($curl);
        curl_close($curl);

        $output = simplexml_load_string($xml, 'SimpleXmlElement', LIBXML_NOERROR);

        if ($output) {
            return $output;
        } else {
            throw new InvalidXmlException();
        }
    }

    /**
     * Test if the given SimpleXMLObject is valid
     *
     * @param $xmlObj the given SimpleXMLElement
     *
     * @return Object SimpleXMLElement or throw an InvalidXmlObjectException
     */
    public function isValidXMLObject($xmlObj)
    {
        if (is_object($xmlObj)) {
            return $xmlObj;
        } else {
            throw new InvalidXmlObjectException();
        }
    }
}
