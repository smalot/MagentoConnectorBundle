<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriterSpec extends ObjectBehavior
{
    function let(WebserviceGuesser $webserviceGuesser, ChannelManager $channelManager)
    {
        $this->beConstructedWith($webserviceGuesser, $channelManager);
    }

    function it_writes_a_product($webserviceGuesser, Webservice $webservice, StepExecution $stepExecution)
    {
        $products = array(
            'batch_1' => array(
                'product_1' => array(
                    'default' => array(
                        'sku'
                    ),
                    'en_US' => array(),
                    'images' => array()
                )
            )
        );

        $this->setStepExecution($stepExecution);
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);
        $webservice->getImages('sku', 'default')->willReturn(array());
        $webservice->sendProduct(array('sku'))->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('Products sent')->shouldBeCalled();
        $webservice->sendImages(Argument::any())->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('Products images sent')->shouldBeCalledTimes(2);
        $webservice->updateProductPart(Argument::any())->shouldBeCalled();

        $this->write($products);
    }

    function it_fails_if_something_went_wrong_when_it_sends_a_product(
        $webserviceGuesser,
        Webservice $webservice,
        StepExecution $stepExecution
    ) {
        $products = array(
            'batch_1' => array(
                'product_1' => array(
                    'default' => array(
                        'sku'
                    ),
                    'en_US' => array(),
                    'images' => array()
                )
            )
        );

        $this->setStepExecution($stepExecution);
        $webserviceGuesser->getWebservice(Argument::any())->willReturn($webservice);
        $webservice->getImages('sku', 'default')->willReturn(array());
        $webservice->sendProduct(array('sku'))->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');
        $stepExecution->incrementSummaryInfo('Products sent')->shouldNotBeCalled();
        $webservice->sendImages(Argument::any())->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('Products images sent')->shouldNotBeCalled();
        $webservice->updateProductPart(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringWrite($products);
    }

    function it_gives_a_configuration_field()
    {
        $this->getConfigurationFields()->shouldReturn(
            array(
                'soapUsername' => array(
                    'options' => array(
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.soapUsername.help',
                        'label'    => 'pim_magento_connector.export.soapUsername.label'
                    )
                ),
                'soapApiKey'   => array(
                    'type'    => 'text',
                    'options' => array(
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.soapApiKey.help',
                        'label'    => 'pim_magento_connector.export.soapApiKey.label'
                    )
                ),
                'magentoUrl' => array(
                    'options' => array(
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.magentoUrl.help',
                        'label'    => 'pim_magento_connector.export.magentoUrl.label'
                    )
                ),
                'wsdlUrl' => array(
                    'options' => array(
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.wsdlUrl.help',
                        'label'    => 'pim_magento_connector.export.wsdlUrl.label',
                        'data'     => '/api/soap/?wsdl'
                    )
                ),
                'httpLogin' => array(
                    'options' => array(
                        'required' => false,
                        'help'     => 'pim_magento_connector.export.httpLogin.help',
                        'label'    => 'pim_magento_connector.export.httpLogin.label'
                    )
                ),
                'httpPassword' => array(
                    'options' => array(
                        'required' => false,
                        'help'     => 'pim_magento_connector.export.httpPassword.help',
                        'label'    => 'pim_magento_connector.export.httpPassword.label'
                    )
                ),
                'defaultStoreView' => array(
                    'options' => array(
                        'required' => false,
                        'help'     => 'pim_magento_connector.export.defaultStoreView.help',
                        'label'    => 'pim_magento_connector.export.defaultStoreView.label',
                        'data'     => 'default'
                    )
                ),
                'channel' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => null,
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.channel.help',
                        'label'    => 'pim_magento_connector.export.channel.label'
                    )
                )
            )
        );
    }
}
