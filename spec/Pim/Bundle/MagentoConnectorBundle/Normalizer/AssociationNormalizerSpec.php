<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\MagentoConnectorBundle\Helper\ValidProductHelper;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\MandatoryAttributeNotFoundException;

class AssociationNormalizerSpec extends ObjectBehavior
{
    public function let(ValidProductHelper $validProductHelper)
    {
        $this->beConstructedWith($validProductHelper);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Normalizer\AssociationNormalizer');
    }

    public function it_returns_true_if_the_normalizer_can_support_given_data(Association $association)
    {
        $this->supportsNormalization($association, 'api_import')->shouldReturn(true);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data($object)
    {
        $this->supportsNormalization($object, 'api_import')->shouldReturn(false);
    }

    public function it_returns_false_if_the_normalizer_can_not_support_given_data_because_of_the_format(
        Association $association
    ) {
        $this->supportsNormalization($association, 'foo_bar')->shouldReturn(false);
    }

    public function it_normalizes_an_association_in_array_with_the_api_import_format(
        Association $association,
        AssociationType $associationType,
        ProductInterface $product,
        Collection $productColl,
        Channel $channel,
        $validProductHelper
    ) {

        $context = [
            'channel' => $channel,
            'defaultStoreView'    => 'Default',
            'defaultLocale'       => 'en_US',
            'website'             => 'base',
            'defaultCurrency'     => 'USD',
            'visibility'          => '4',
            'enabled'             => '1',
            'storeViewMapping'    => [
                'fr_FR' => 'fr_fr'
            ],
            'associationMapping'  => [
                'UPSELL'  => 'upsell',
                'X_SELL'  => 'crosssell',
                'RELATED' => 'related',
                'PACK'    => ''
            ],
            'attributeMapping'    => [
                'sku'               => 'sku',
                'name'              => 'name',
                'description'       => 'description',
                'short_description' => 'short_description',
                'status'            => 'enabled'
            ]
        ];

        $validProductHelper->getValidProducts($channel, $productColl)->willReturn([$product]);

        $product->getIdentifier()->willReturn('sku foo');

        $association->getProducts()->willReturn($productColl);
        $association->getAssociationType()->willReturn($associationType);

        $associationType->getCode()->willReturn('X_SELL');

        $this->normalize($association, 'api_import', $context)->shouldReturn([
            [
                '_links_crosssell_sku' => 'sku foo'
            ]
        ]);
    }
}
