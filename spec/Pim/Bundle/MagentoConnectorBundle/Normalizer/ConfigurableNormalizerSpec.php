<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\PriceMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\ProductNormalizer;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigurableNormalizerSpec extends ObjectBehavior
{
    protected $globalContext = array();

    function let(
        ChannelManager $channelManager,
        ProductNormalizer $productNormalizer,
        PriceMappingManager $priceMappingManager,
        MappingCollection $categoryMapping,
        MappingCollection $attributeMapping,
        MappingCollection $storeViewMapping,
        ProductInterface $product,
        Channel $channel,
        Locale $localeFR,
        Locale $localeEN,
        Group $group
    ) {
        $this->beConstructedWith($channelManager, $productNormalizer, $priceMappingManager);

        $this->globalContext = array(
            'attributeSetId'           => 0,
            'magentoAttributes'        => array(),
            'magentoAttributesOptions' => array(),
            'storeViewMapping'         => $storeViewMapping,
            'magentoStoreViews'        => array(array('code' => 'fr_fr')),
            'defaultLocale'            => 'default_locale',
            'website'                  => 'website',
            'channel'                  => 'channel',
            'categoryMapping'          => $categoryMapping,
            'attributeMapping'         => $attributeMapping,
            'create'                   => true
        );

        $productNormalizer->getValues(Argument::cetera())->willReturn(array());
        $productNormalizer->getNormalizedImages($product)->willReturn(array());

        $channelManager->getChannelByCode('channel')->willReturn($channel);
        $channel->getLocales()->willReturn(array($localeEN, $localeFR));
        $localeEN->getCode()->willReturn('default_locale');
        $localeFR->getCode()->willReturn('fr_FR');
        $channel->getCode()->willReturn('channel_code');

        $storeViewMapping->getTarget('default_locale')->willReturn('default_locale');
        $storeViewMapping->getTarget('fr_FR')->willReturn('fr_fr');

        $group->getCode()->willReturn('group_code');
        $product->getIdentifier()->willReturn('sku-000');
    }

    function it_normalizes_a_new_configurable_product($group, $product, $priceMappingManager)
    {
        $products = array($product);

        $priceMappingManager->getPriceMapping($group, $products)->willReturn(array('price_changes' => array(), 'price' => array()));
        $priceMappingManager->validatePriceMapping($products, array(), array())->willReturn(true);

        $this->normalize(array(
            'group'    => $group,
            'products' => $products
        ), 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'default' => array(
                'configurable',
                0,
                'conf-group_code',
                array(
                    'price_changes'   => array(),
                    'price'           => array(),
                    'associated_skus' => array('sku-000'),
                    'websites'        => array('website')
                )
            ),
            'images' => array(),
            'fr_fr'  => array(
                'conf-group_code',
                array(),
                'fr_fr'
            )
        ));
    }

    function it_normalizes_a_updated_configurable_product($group, $product, $priceMappingManager)
    {
        $this->globalContext['create'] = false;

        $products = array($product);

        $priceMappingManager->getPriceMapping($group, $products)->willReturn(array('price_changes' => array(), 'price' => array()));
        $priceMappingManager->validatePriceMapping($products, array(), array())->willReturn(true);

        $this->normalize(array(
            'group'    => $group,
            'products' => $products
        ), 'MagentoArray', $this->globalContext)->shouldReturn(array(
            'default' => array(
                'conf-group_code',
                array(
                    'price_changes'   => array(),
                    'price'           => array(),
                    'associated_skus' => array('sku-000'),
                    'websites'        => array('website')
                )
            ),
            'images' => array(),
            'fr_fr'  => array(
                'conf-group_code',
                array(),
                'fr_fr'
            )
        ));
    }

    function it_raises_an_expcetion_if_the_locale_does_not_have_a_corresponding_storeview($group, $product, $priceMappingManager)
    {
        $this->globalContext['create']            = false;
        $this->globalContext['magentoStoreViews'] = array();

        $products = array($product);

        $priceMappingManager->getPriceMapping($group, $products)->willReturn(array('price_changes' => array(), 'price' => array()));
        $priceMappingManager->validatePriceMapping($products, array(), array())->willReturn(true);

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\LocaleNotMatchedException')->during('normalize', array(array(
            'group'    => $group,
            'products' => $products
        ), 'MagentoArray', $this->globalContext));
    }

    function it_raises_an_expcetion_if_the_price_mapping_is_not_valid($group, $product, $priceMappingManager)
    {
        $this->globalContext['create'] = false;

        $products = array($product);

        $priceMappingManager->getPriceMapping($group, $products)->willReturn(array('price_changes' => array(), 'price' => array()));
        $priceMappingManager->validatePriceMapping($products, array(), array())->willThrow('Pim\Bundle\MagentoConnectorBundle\Manager\ComputedPriceNotMatchedException');

        $this->shouldThrow('Pim\Bundle\MagentoConnectorBundle\Normalizer\Exception\InvalidPriceMappingException')->during('normalize', array(array(
            'group'    => $group,
            'products' => $products
        ), 'MagentoArray', $this->globalContext));
    }
}
