<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

class ProductPriceNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductPriceNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(ProductPrice $productPrice)
    {
        $this->supportsNormalization($productPrice, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        ProductPrice $productPrice
    ) {
        $this->supportsNormalization($productPrice, 'foo_bar')->shouldReturn(false);
    }

    public function it_normalizes_a_product_price_to_the_api_import_format(ProductPrice $productPrice)
    {
        $productPrice->getCurrency()->willReturn('USD');
        $productPrice->getData()->willReturn((double) 42.42);

        $this->normalize($productPrice, 'api_import', ['defaultCurrency' => 'USD'])->shouldReturn((double) 42.42);
    }

    public function it_returns_null_if_the_default_currency_is_not_the_same_as_product_price(ProductPrice $productPrice)
    {
        $productPrice->getCurrency()->willReturn('EUR');
        $productPrice->getData()->willReturn((double) 42.42);

        $this->normalize($productPrice, 'api_import', ['defaultCurrency' => 'USD'])->shouldReturn(null);
    }
}
