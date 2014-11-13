<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Reader;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;

class VariantGroupReaderSpec extends ObjectBehavior
{
    public function let(ProductReaderInterface $productReader)
    {
        $this->beConstructedWith($productReader);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Reader\VariantGroupReader');
    }
}
