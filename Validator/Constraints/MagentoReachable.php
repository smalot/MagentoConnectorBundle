<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * This constraint allows to validate if Magento is reachable with parameters of MagentoConfiguration entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoReachable extends Constraint
{
    public $messageNotReachableUrl = '
        <error>URL unreachable. Check the Magento URL.</error>

        <error>[EXCEPTION]: %EXCEPTION%</error>

        <comment>[INVALID VALUES]:</comment>
        <comment>SOAP URL: %SOAP_URL%</comment>
        <comment>HTTP LOGIN: %HTTP_LOGIN%</comment>
        <comment>HTTP PASSWORD: %HTTP_PASSWD%</comment>';

    public $messageInvalidSoapUrl = '
        <error>SOAP URL unreachable. Check the Magento SOAP URL and its wsdl extension.</error>

        <error>[EXCEPTION]: %EXCEPTION%</error>

        <comment>[INVALID VALUES]:</comment>
        <comment>SOAP URL: %SOAP_URL%</comment>';

    public $messageAccessDenied = '
        <error>Access denied to Magento. Verify your SOAP username and API key.</error>

        <error>[EXCEPTION]: %EXCEPTION%</error>

        <comment>[INVALID VALUES]:</comment>
        <comment>SOAP USERNAME: %USERNAME%</comment>
        <comment>SOAP API KEY: %API_KEY%</comment>';

    public $messageXmlNotValid = '
        <error>Response content type is not XML. Check the Magento SOAP URL and its wsdl extension.</error>

        <comment>[INVALID VALUES]:</comment>
        <comment>SOAP URL: %SOAP_URL%</comment>';

    public $messageUndefinedSoapException  = '
        <error>The problem you encountered is not listed. Please, refer to the following error.</error>

        <error>[EXCEPTION]: %EXCEPTION%</error>

        <comment>[VALUES]:</comment>
        <comment>SOAP URL: %SOAP_URL%</comment>
        <comment>HTTP LOGIN: %HTTP_LOGIN%</comment>
        <comment>HTTP PASSWORD: %HTTP_PASSWD%</comment>
        <comment>SOAP USERNAME: %USERNAME%</comment>
        <comment>SOAP API KEY: %API_KEY%</comment>';

    /**
     * Returns alias of the MagentoReachable service
     *
     * @return string
     */
    public function validatedBy()
    {
        return 'magento_reachable';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
