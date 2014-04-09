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
        if (!$this->isValidMagentoUrl($value)) {
            $this->context->addViolation($constraint->message, array('%string%' => $value));
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
        if (substr($url, strlen($url)-1,1) != '/') {
            return false;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . MagentoSoapClient::SOAP_WSDL_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        try {
            $xml = simplexml_load_string($output);
        } catch (\Exception $e) {
            return false;
        }

        return is_object($xml);
    }
}
