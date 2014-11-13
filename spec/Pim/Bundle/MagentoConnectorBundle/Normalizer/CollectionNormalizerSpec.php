<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Prophecy\Argument;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class CollectionNormalizerSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\CollectionNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(Collection $collection)
    {
        $this->supportsNormalization($collection, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        Collection $collection
    ) {
        $this->supportsNormalization($collection, 'foo_bar')->shouldReturn(false);
    }

    public function it_sets_serializer_as_a_normalizer(Serializer $serializer)
    {
        $this->setSerializer($serializer)->shouldReturn(null);
    }

    public function it_does_not_set_an_object_as_a_normalizer(SerializerInterface $object)
    {
        $this->shouldThrow('\LogicException')->during('setSerializer', [$object]);
    }

    public function it_normalizes_a_collection_in_api_import_format(
        AttributeOption $option,
        Serializer $normalizer
    ) {
        $collection = new ArrayCollection([$option]);
        $normalizer->normalize($option, 'api_import', [])->willReturn('foo');

        $this->setSerializer($normalizer);
        $this->normalize($collection, 'api_import', [])->shouldReturn(['foo']);
    }

    public function it_normalizes_a_price_collection_to_the_api_import_format(
        ProductPrice $price,
        Serializer $normalizer,
        ArrayCollection $collection
    ) {
        $context = ['defaultCurrency' => 'USD'];

        $collection->first()->willReturn($price);
        $collection->get('USD')->willReturn($price);
        $collection->getIterator()->shouldNotBeCalled();

        $normalizer->normalize($price, 'api_import', $context)->shouldBeCalled()->willReturn((double) 42);

        $this->setSerializer($normalizer);
        $this->normalize($collection, 'api_import', $context)->shouldReturn((double) 42);
    }

    public function it_returns_null_if_there_is_nothing_to_normalize_in_the_collection(
        Serializer $normalizer
    ) {
        $collection = new ArrayCollection([]);
        $normalizer->normalize(Argument::cetera())->shouldNotBeCalled();

        $this->setSerializer($normalizer);
        $this->normalize($collection, 'api_import', [])->shouldReturn(null);
    }
}
