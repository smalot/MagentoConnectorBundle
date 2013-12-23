<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;

class IsValidWsdlUrlValidator extends ConstraintValidator
{
    /**
     *{@inheritDoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$this->isValidWsdlUrl($value)) {
            $this->context->addViolation($constraint->message, array('%string%' => $value));
        }
    }

    /**
     * Test if the given base url leads to a valid wsdl url
     *
     * @param  string  $url The given url
     * @return boolean
     */
    public function isValidWsdlUrl($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url . MagentoSoapClient::SOAP_WSDL_URL);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return is_object(simplexml_load_string($output));
    }
}
