<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\ConstraintValidator;

use PhpSpec\ObjectBehavior;

class HasValidApiUrlValidatorSpec extends ObjectBehavior
{

    function it_should_fail_with_url_without_slashs()
    {
        $this->isWellFormedUrl('http://valid.url', 'valid/api/path')->shouldReturn(false);
    }

    function it_should_fail_with_urls_with_slashs() {
        $this->isWellFormedUrl('http://valid.url/', '/valid/api/path/')->shouldReturn(false);
    }

    function it_should_work_with_magento_url_with_slash()
    {
        $this->isWellFormedUrl('http://valid.url/', 'valid/api/path')->shouldReturn(true);
    }

    function it_should_work_with_wsdl_url_with_slash()
    {
        $this->isWellFormedUrl('http://valid.url', '/valid/api/path')->shouldReturn(true);
    }

}
