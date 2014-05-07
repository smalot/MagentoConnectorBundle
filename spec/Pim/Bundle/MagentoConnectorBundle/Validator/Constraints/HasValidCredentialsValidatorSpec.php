<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidXmlException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapExplorer;
use Pim\Bundle\MagentoConnectorBundle\Validator\Checks\XmlChecker;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Specification of class HasValidCredentialsValidator
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HasValidCredentialsValidatorSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        SoapExplorer $soapExplorer,
        XmlChecker $xmlChecker,
        ExecutionContextInterface $context
    ) {
        $this->beConstructedWith($webserviceGuesser, $soapExplorer, $xmlChecker);

        $this->initialize($context);
    }

    function it_does_nothing_with_something_else_than_magento_item_step(
        $context,
        AbstractConfigurableStepElement $step,
        Constraint $constraint
    ) {
        $context->addViolationAt(Argument::cetera())->shouldNotBeCalled();

        $this->validate($step, $constraint);
    }

    function it_success_with_good_credentials_and_valid_soap_url(
        $context,
        MagentoItemStep $step,
        Constraint $constraint
    ) {
        $context->addViolationAt(Argument::cetera())->shouldNotBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_if_soap_url_is_not_reachable(
        $context,
        $soapExplorer,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $constraint->messageUrlNotReachable = 'pim_magento_connector.export.validator.url_not_reachable';
        $soapExplorer->getSoapUrlContent(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException');
        $context->addViolationAt('wsdlUrl', 'pim_magento_connector.export.validator.url_not_reachable')->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_with_invalid_soap_url_or_wrong_http_authentication_credentials(
        $context,
        $soapExplorer,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $constraint->messageSoapNotValid = 'pim_magento_connector.export.validator.soap_url_not_valid';
        $soapExplorer->getSoapUrlContent(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException');
        $context->addViolationAt('wsdlUrl', 'pim_magento_connector.export.validator.soap_url_not_valid')->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_with_invalid_soap_xml_return(
        $context,
        $xmlChecker,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $constraint->messageXmlNotValid = 'pim_magento_connector.export.validator.xml_not_valid';
        $xmlChecker->checkXml(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidXmlException');
        $context->addViolationAt('wsdlUrl', 'pim_magento_connector.export.validator.xml_not_valid')->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_with_invalid_soap_credentials_or_user_has_no_right_on_magento(
        $context,
        $webserviceGuesser,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $constraint->messageUsername = 'pim_magento_connector.export.validator.authentication_failed';
        $webserviceGuesser->getWebservice(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException');
        $context->addViolationAt('soapUsername', 'pim_magento_connector.export.validator.authentication_failed')->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_if_an_unknown_error_occured(
        $context,
        $webserviceGuesser,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');
        $context->addViolationAt('soapUsername', Argument::any())->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_returns_true_with_good_credentials_and_valid_soap_url(
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->areValidSoapCredentials($clientParameters)->shouldReturn(true);
    }

    function it_returns_false_with_invalid_soap_url_or_wrong_http_authentication_credentials(
        $soapExplorer,
        MagentoSoapClientParameters $clientParameters
    ) {
        $soapExplorer->getSoapUrlContent(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException');

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(false);
    }

    function it_returns_false_if_soap_url_is_not_reachable(
        $soapExplorer,
        MagentoSoapClientParameters $clientParameters
    ) {
        $soapExplorer->getSoapUrlContent(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException');

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(false);
    }

    function it_returns_false_with_invalid_soap_credentials_or_user_has_no_right_on_magento(
        $webserviceGuesser,
        MagentoSoapClientParameters $clientParameters
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException');

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(false);
    }

    function it_returns_false_if_an_unknown_error_occured(
        $webserviceGuesser,
        MagentoSoapClientParameters $clientParameters
    ) {
        $webserviceGuesser->getWebservice(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(false);
    }
}
