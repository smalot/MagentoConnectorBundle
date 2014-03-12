<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
use Pim\Bundle\MagentoConnectorBundle\Mapper\MappingCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class OptionNormalizerSpec extends ObjectBehavior
{
    function let(ChannelManager $channelManager, Channel $channel)
    {
        $this->beConstructedWith($channelManager);

        $channelManager->getChannelByCode('magento')->willReturn($channel);
    }

    function it_normalizes_given_option(
        AttributeOption $option,
        AttributeOption $optionUS,
        AttributeOption $optionFR,
        AttributeOption $optionDE,
        AttributeOptionValue $optionValueUS,
        AttributeOptionValue $optionValueFR,
        AttributeOptionValue $optionValueDE,
        Locale $localeUS,
        Locale $localeFR,
        Locale $localeDE,
        Attribute $attribute,
        $channel,
        MappingCollection $storeViewMapping
    ) {
        $magentoStoreViews = array(
            array('code' => 'default', 'store_id' => 1),
            array('code' => 'fr_fr',   'store_id' => 2),
            array('code' => 'test',    'store_id' => 3),
        );

        $storeViewMapping->getTarget('en_US')->willReturn('en_us');
        $storeViewMapping->getTarget('fr_FR')->willReturn('fr_fr');
        $storeViewMapping->getTarget('de_DE')->willReturn('test');

        $option->getCode()->willReturn('red');

        $option->getOptionValues()->willReturn(array($optionValueUS, $optionValueFR, $optionValueDE));

        $option->getSortOrder()->willReturn(1);

        $option->setLocale('en_US')->willReturn($optionUS);
        $optionUS->getOptionValue()->willReturn($optionValueUS);
        $optionValueUS->getLocale()->willReturn('en_US');
        $optionValueUS->getLabel()->willReturn('Red');

        $option->setLocale('fr_FR')->willReturn($optionFR);
        $optionFR->getOptionValue()->willReturn($optionValueFR);
        $optionValueFR->getLocale()->willReturn('fr_FR');
        $optionValueFR->getLabel()->willReturn('Rouge');

        $option->setLocale('de_DE')->willReturn($optionDE);
        $optionDE->getOptionValue()->willReturn($optionValueDE);
        $optionValueDE->getLocale()->willReturn('de_DE');
        $optionValueDE->getLabel()->willReturn('Rot');

        $option->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');

        $this->normalize($option, Argument::any(), array(
            'magentoStoreViews' => $magentoStoreViews,
            'storeViewMapping'  => $storeViewMapping,
            'channel'           => 'magento',
            'defaultLocale'     => 'en_US',
            'attributeCode'     => 'attribute_code'
        ))->shouldReturn(array(
            'attribute_code',
            array(
                'label' => array(
                    array(
                        'store_id' => '0',
                        'value'    => 'red'
                    ),
                    array(
                        'store_id' => '1',
                        'value'    => 'Red'
                    ),
                    array(
                        'store_id' => '2',
                        'value'    => 'Rouge'
                    ),
                    array(
                        'store_id' => '3',
                        'value'    => 'Rot'
                    ),
                ),
                'order'      => 1
            )
        ));
    }
}
