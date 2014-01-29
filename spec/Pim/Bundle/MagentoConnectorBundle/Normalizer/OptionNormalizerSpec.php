<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Attribute;
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
        $channel
    ) {
        $magentoStoreViews = array(
            array('code' => 'default', 'store_id' => 1),
            array('code' => 'fr_fr',   'store_id' => 2),
            array('code' => 'test',    'store_id' => 3),
        );

        $storeViewMapping = array(
            'de_de' => 'test'
        );

        $option->getCode()->willReturn('red');

        $option->setLocale('en_US')->willReturn($optionUS);
        $optionUS->getOptionValue()->willReturn($optionValueUS);
        $optionValueUS->getLabel()->willReturn('Red');

        $option->setLocale('fr_FR')->willReturn($optionFR);
        $optionFR->getOptionValue()->willReturn($optionValueFR);
        $optionValueFR->getLabel()->willReturn('Rouge');

        $option->setLocale('de_DE')->willReturn($optionDE);
        $optionDE->getOptionValue()->willReturn($optionValueDE);
        $optionValueDE->getLabel()->willReturn('Rot');

        $localeUS->getCode()->willReturn('en_US');
        $localeFR->getCode()->willReturn('fr_FR');
        $localeDE->getCode()->willReturn('de_DE');

        $channel->getLocales()->willReturn(array($localeUS, $localeFR, $localeDE));

        $option->getAttribute()->willReturn($attribute);
        $attribute->getCode()->willReturn('color');

        $this->normalize($option, Argument::any(), array(
            'magentoStoreViews' => $magentoStoreViews,
            'storeViewMapping'  => $storeViewMapping,
            'channel'           => 'magento',
            'defaultLocale'     => 'en_US'
        ))->shouldReturn(array(
            'color',
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
                'order'      => 0,
                'is_default' => 0
            )
        ));
    }
}
