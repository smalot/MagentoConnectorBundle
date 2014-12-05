<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Model\ProductMedia;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper;

class ProductMediaNormalizerSpec extends ObjectBehavior
{
    public function let(MediaManager $mediaManager)
    {
        $attributesHelper = new MagentoAttributesHelper();
        $this->beConstructedWith($mediaManager, $attributesHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductMediaNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(ProductMedia $productMedia)
    {
        $this->supportsNormalization($productMedia, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        ProductMedia $productMedia
    ) {
        $this->supportsNormalization($productMedia, 'foo_bar')->shouldReturn(false);
    }

    public function it_normalizes_a_product_media_to_the_api_import_format(
        ProductMedia $productMedia,
        ProductValue $productValue,
        Attribute $attribute,
        $mediaManager
    ) {
        $productMedia->getValue()->willReturn($productValue);
        $productMedia->getFilename()->willReturn('my_media_name');
        $productValue->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('media_attribute_code');
        $mediaManager->getBase64($productMedia)->willReturn('base64_code_of_the_media');

        $this->normalize($productMedia, 'api_import', [])->shouldReturn([
            [
                'media_attribute_code' => 'my_media_name',
                'media_attribute_code_content' => 'base64_code_of_the_media',
                '_media_image' => 'my_media_name',
                '_media_is_disabled' => 0
            ]
        ]);
    }
}
