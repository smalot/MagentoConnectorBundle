<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException;
use Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidXmlException;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Pim\Bundle\MagentoConnectorBundle\Webservice\UrlExplorer;
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
        WebserviceGuesser                   $webserviceGuesser,
        UrlExplorer                         $urlExplorer,
        XmlChecker                          $xmlChecker,
        ExecutionContextInterface           $context,
        MagentoSoapClientParametersRegistry $clientParameters
    ) {
        $this->beConstructedWith($webserviceGuesser, $urlExplorer, $xmlChecker);

        $clientParameters = MagentoSoapClientParametersRegistry::getInstance(null, null, null, null, null);
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
        $clientParameters,
        MagentoItemStep $step,
        Constraint $constraint
    ) {
        $clientParameters->isValid()->willReturn(true);
        $context->addViolationAt(Argument::cetera())->shouldNotBeCalled();
        $clientParameters->setValidation(Argument::any())->shouldNotBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_if_soap_url_is_not_reachable(
        $context,
        $urlExplorer,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance(null, null, null, null, null);
        $clientParameters->setValidation(false);
        $constraint->messageUrlNotReachable = 'pim_magento_connector.export.validator.url_not_reachable';
        $urlExplorer->getUrlContent($clientParameters)->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException');
        $context->addViolationAt('wsdlUrl', 'pim_magento_connector.export.validator.url_not_reachable ""')->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_with_invalid_soap_url_or_wrong_http_authentication_credentials(
        $context,
        $urlExplorer,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance(null, null, null, null, null);
        $clientParameters->setValidation(false);
        $constraint->messageSoapNotValid = 'pim_magento_connector.export.validator.soap_url_not_valid';
        $urlExplorer->getUrlContent($clientParameters)->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException');
        $context->addViolationAt('wsdlUrl', 'pim_magento_connector.export.validator.soap_url_not_valid ""')->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_with_invalid_soap_xml_return(
        $context,
        $xmlChecker,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance(null, null, null, null, null);
        $clientParameters->setValidation(false);
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
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance(null, null, null, null, null);
        $clientParameters->setValidation(false);
        $constraint->messageUsername = 'pim_magento_connector.export.validator.authentication_failed';
        $webserviceGuesser->getWebservice($clientParameters)->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException');
        $context->addViolationAt('soapUsername', 'pim_magento_connector.export.validator.authentication_failed')->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_fails_if_an_unknown_error_occured(
        $context,
        $webserviceGuesser,
        MagentoItemStep $step,
        HasValidCredentials $constraint
    ) {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance(null, null, null, null, null);
        $clientParameters->setValidation(false);
        $webserviceGuesser->getWebservice($clientParameters)->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');
        $context->addViolationAt('soapUsername', Argument::any())->shouldBeCalled();

        $this->validate($step, $constraint);
    }

    function it_returns_true_with_good_credentials_and_valid_soap_url()
    {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('soap_username', 'soap_api_key', 'http://magento.url', '/api/soap/?wsdl', 'default');
        $clientParameters->setValidation(null);
        $this->areValidSoapCredentials($clientParameters)->shouldReturn(true);
    }

    function it_returns_false_with_invalid_soap_url_or_wrong_http_authentication_credentials($urlExplorer)
    {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('soap_username', 'soap_api_key', 'http://badmagentourl', '/apoap/?wsdl', 'default');
        $clientParameters->setValidation(null);
        $urlExplorer->getUrlContent(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\InvalidSoapUrlException');

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(false);
    }

    function it_returns_false_if_soap_url_is_not_reachable($urlExplorer)
    {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('soap_username', 'soap_api_key', 'http://magento.url', '/apoapwsdl', 'default');
        $clientParameters->setValidation(null);
        $urlExplorer->getUrlContent(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Validator\Exception\NotReachableUrlException');

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(false);
    }

    function it_returns_false_with_invalid_soap_credentials_or_user_has_no_right_on_magento($webserviceGuesser)
    {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('wrong_username', 'soap_api_key', 'http://magento.url', '/api/soap/?wsdl', 'default');
        $clientParameters->setValidation(null);
        $webserviceGuesser->getWebservice(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\InvalidCredentialException');

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(false);
    }

    function it_returns_false_if_an_unknown_error_occured($webserviceGuesser)
    {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance(null, null, null, null, null);
        $clientParameters->setValidation(null);
        $webserviceGuesser->getWebservice(Argument::any())->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(false);
    }

    function it_returns_state_of_client_parameters_if_it_is_already_set()
    {
        $clientParameters = MagentoSoapClientParametersRegistry::getInstance('wrong_username', 'soap_api_key', 'http://magento.url', '/api/soap/?wsdl', 'default');
        $clientParameters->setValidation(true);

        $this->areValidSoapCredentials($clientParameters)->shouldReturn(true);
    }
}
