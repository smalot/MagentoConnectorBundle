<?php

namespace Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Checks\UrlChecker;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapExplorer;
use Pim\Bundle\MagentoConnectorBundle\Validator\Checks\XmlChecker;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidXmlException;

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
     * @var UrlChecker
     */
    protected $urlChecker;

    /**
     * @var SoapExplorer
     */
    protected $soapExplorer;

    /**
     * @var XmlChecker
     */
    protected $xmlChecker;

    /**
     * @var array
     */
    protected $valid;

    /**
     * @param WebserviceGuesser $webserviceGuesser
     * @param UrlChecker        $urlChecker
     * @param SoapExplorer      $soapExplorer
     * @param XmlChecker        $xmlChecker
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        UrlChecker $urlChecker,
        SoapExplorer $soapExplorer,
        XmlChecker $xmlChecker
    ) {
        $this->webserviceGuesser = $webserviceGuesser;
        $this->urlChecker        = $urlChecker;
        $this->soapExplorer      = $soapExplorer;
        $this->xmlChecker        = $xmlChecker;
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

        $objectId = spl_object_hash($clientParameters);

        if (!isset($this->valid[$objectId]) || false === $this->valid[$objectId]) {

            try {
                $this->urlChecker->checkAnUrl($clientParameters->getMagentoUrl());
                $this->urlChecker->checkReachableUrl($clientParameters->getMagentoUrl());
                $xml = $this->soapExplorer->getSoapUrlContent($clientParameters->getSoapUrl());
                $this->xmlChecker->checkXml($xml);
                $this->webserviceGuesser->getWebservice($clientParameters);
            } catch (InvalidUrlException $e) {
                $this->context->addViolationAt('magentoUrl', $constraint->messageUrlSyntaxNotValid, array());
            } catch (NotReachableUrlException $e) {
                $this->context->addViolationAt('magentoUrl', $constraint->messageUrlNotReachable, array());
            } catch (InvalidSoapUrlException $e) {
                $this->context->addViolationAt('wsdlUrl', $constraint->messageSoapNotValid, array());
            } catch (InvalidXmlException $e) {
                $this->context->addViolationAt('wsdlUrl', $constraint->messageXmlNotValid, array());
            } catch (InvalidCredentialException $e) {
                $this->context->addViolationAt('soapUsername', $constraint->messageUsername, array());
            } catch (SoapCallException $e) {
                $this->context->addViolationAt('soapUsername', $e->getMessage(), array());
            }

        }
    }

    /**
     * Are the given parameters valid ?
     *
     * @param MagentoSoapClientParameters $clientParameters
     *
     * @return boolean
     */
    public function areValidSoapCredentials(MagentoSoapClientParameters $clientParameters)
    {
        $objectId = spl_object_hash($clientParameters);

        if (!isset($this->valid[$objectId])) {

            try {
                $this->soapExplorer->getSoapUrlContent($clientParameters->getSoapUrl());
                $this->webserviceGuesser->getWebservice($clientParameters);
                $this->valid[$objectId] = true;
            } catch (NotReachableUrlException $e) {
                $this->valid[$objectId] = false;
            } catch (InvalidSoapUrlException $e) {
                $this->valid[$objectId] = false;
            } catch (InvalidCredentialException $e) {
                $this->valid[$objectId] = false;
            } catch (SoapCallException $e) {
                $this->valid[$objectId] = false;
            }
        }

        return $this->valid[$objectId];
    }
}
