<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\MagentoConnectorBundle\Helper\MagentoAttributesHelper;
use Pim\Bundle\MagentoConnectorBundle\Helper\ValidProductHelper;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\MandatoryAttributeNotFoundException;

class AssociationNormalizerSpec extends ObjectBehavior
{
    public function let(ValidProductHelper $validProductHelper)
    {
        $attributeHelper = new MagentoAttributesHelper();
        $this->beConstructedWith($attributeHelper, $validProductHelper);
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
        ProductInterface $ownerProduct,
        Collection $productColl,
        ProductValue $productValueSku,
        ProductValue $productValueDescription,
        ProductValue $productValueShortDesc,
        ProductValue $productValueName,
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

        $ownerProduct->getValue('sku', 'en_US', 'magento')->willReturn($productValueSku);
        $ownerProduct->getValue('description', 'en_US', 'magento')->willReturn($productValueDescription);
        $ownerProduct->getValue('short_description', 'en_US', 'magento')->willReturn($productValueShortDesc);
        $ownerProduct->getValue('name', 'en_US', 'magento')->willReturn($productValueName);
        $product->getIdentifier()->willReturn('sku foo');

        $productValueSku->getData()->willReturn('sku owner');
        $productValueDescription->getData()->willReturn('Description owner');
        $productValueShortDesc->getData()->willReturn('Short description owner');
        $productValueName->getData()->willReturn('Name owner');

        $association->getProducts()->willReturn($productColl);
        $association->getAssociationType()->willReturn($associationType);
        $association->getOwner()->willReturn($ownerProduct);

        $channel->getCode()->willReturn('magento');

        $associationType->getCode()->willReturn('X_SELL');

        $this->normalize($association, 'api_import', $context)->shouldReturn([
            [
                'sku' => 'sku owner',
                'description' => 'Description owner',
                'short_description' => 'Short description owner',
                'name' => 'Name owner',
                'status' => '1'
            ],
            [
                '_links_crosssell_sku' => 'sku foo'
            ]
        ]);
    }

    public function it_throws_an_error_during_normalization_if_a_mandatory_attribute_is_not_found_in_product(
        Association $association,
        AssociationType $associationType,
        ProductInterface $product,
        ProductInterface $ownerProduct,
        Collection $productColl,
        Channel $channel,
        $validProductHelper
    ) {

        $context = [
            'channel' => $channel,
            'defaultLocale'       => 'en_US',
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

        $ownerProduct->getValue('sku', 'en_US', 'magento')->willReturn(null);
        $ownerProduct->getIdentifier()->willReturn('sku foo');

        $association->getAssociationType()->willReturn($associationType);
        $association->getProducts()->willReturn($productColl);
        $association->getOwner()->willReturn($ownerProduct);

        $channel->getCode()->willReturn('magento');

        $associationType->getCode()->willReturn('X_SELL');

        $this->shouldThrow(
            new MandatoryAttributeNotFoundException(
                'Mandatory attribute with code "sku" not found in product "sku foo" during association creation.'
            )
        )->duringNormalize($association, 'api_import', $context);
    }
}
