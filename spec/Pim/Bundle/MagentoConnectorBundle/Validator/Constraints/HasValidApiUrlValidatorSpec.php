<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validators\Constraints;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Item\MagentoItemStep;
use Symfony\Component\Validator\Constraint;

use PhpSpec\ObjectBehavior;

class HasValidApiUrlValidatorSpec extends ObjectBehavior
{

    function it_should_fail_with_url_without_slashs(
        MagentoItemStep $magentoItemStep,
        Constraint $constraint//,
        //ExecutionContextInterface $context
    ) {
        $magentoItemStep->getSoapUrl()->willReturn('http://valid.url');
        $magentoItemStep->getWsdlUrl()->willReturn('valid/api/path/');
        //$this->initialize($context);
        $this->validate($magentoItemStep, $constraint)->shouldReturn(null);
        $this->context->addViolation($constraint)->shouldBeCalled();
    }

    function it_should_fail_with_urls_with_slashs(
        MagentoItemStep $magentoItemStep,
        Constraint $constraint,
        ExecutionContextInterface $context
    ) {
        $magentoItemStep->getSoapUrl()->willReturn('http://valid.url/');
        $magentoItemStep->getWsdlUrl()->willReturn('/valid/api/path/');
        $this->initialize($context);
        $this->validate($magentoItemStep, $constraint)->shouldReturn(null);
        $context->addViolation($constraint)->shouldBeCalled();
    }

    function it_should_work_with_soap_url_with_slash(
        MagentoItemStep $magentoItemStep,
        Constraint $constraint,
        ExecutionContextInterface $context
    ) {
        $magentoItemStep->getSoapUrl()->willReturn('http://valid.url/');
        $magentoItemStep->getWsdlUrl()->willReturn('valid/api/path/');
        $this->initialize($context);
        $this->validate($magentoItemStep, $constraint)->shouldReturn(null);
        $context->addViolation($constraint)->shouldNotBeCalled();
    }

    function it_should_work_with_wsdl_url_with_slash(
        MagentoItemStep $magentoItemStep,
        Constraint $constraint,
        ExecutionContextInterface $context
    ) {
        $magentoItemStep->getSoapUrl()->willReturn('http://valid.url');
        $magentoItemStep->getWsdlUrl()->willReturn('/valid/api/path/');
        $this->initialize($context);
        $this->validate($magentoItemStep, $constraint)->shouldReturn(null);
        $context->addViolation($constraint)->shouldBeCalled();
    }

}
