<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParameters;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriterSpec extends ObjectBehavior
{
    function let(
        WebserviceGuesser $webserviceGuesser,
        ChannelManager $channelManager,
        StepExecution $stepExecution,
        Webservice $webservice,
        MagentoSoapClientParametersRegistry $clientParametersRegistry,
        MagentoSoapClientParameters $clientParameters
    ) {
        $this->beConstructedWith($webserviceGuesser, $channelManager, $clientParametersRegistry);
        $this->setStepExecution($stepExecution);

        $clientParametersRegistry->getInstance(null, null, null, '/api/soap/?wsdl', 'default', null, null)->willReturn($clientParameters);
        $webserviceGuesser->getWebservice($clientParameters)->willReturn($webservice);
    }

    function it_updates_a_product($webservice, $stepExecution)
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

        $webservice->getImages('sku', 'default')->willReturn(array());
        $webservice->sendProduct(array('sku'))->shouldBeCalled();
        $webservice->sendImages(Argument::any())->shouldBeCalled();
        $webservice->updateProductPart(Argument::any())->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('Products sent')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('Products images sent')->shouldBeCalledTimes(2);

        $this->write($products);
    }

    function it_creates_a_product($webservice, $stepExecution)
    {
        $products = array(
            'batch_1' => array(
                'product_1' => array(
                    'default' => array(
                        'something',
                        'another',
                        'sku',
                        'again',
                        'lastone'
                    ),
                    'en_US' => array(),
                    'images' => array()
                )
            )
        );

        $webservice->getImages('sku', 'default')->willReturn(array());
        $webservice->sendProduct(array('something', 'another', 'sku', 'again', 'lastone'))->shouldBeCalled();
        $webservice->sendImages(Argument::any())->shouldBeCalled();
        $webservice->updateProductPart(Argument::any())->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('Products images sent')->shouldBeCalledTimes(2);
        $stepExecution->incrementSummaryInfo('Products sent')->shouldBeCalled();

        $this->write($products);
    }

    function it_updates_a_product_and_prunes_old_images($webservice, $stepExecution)
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

        $webservice->getImages('sku', 'default')->willReturn(array(array('file' => 'foo'), array('file' => 'bar')));
        $webservice->deleteImage('sku','foo')->shouldBeCalled();
        $webservice->deleteImage('sku','bar')->shouldBeCalled();
        $webservice->sendProduct(array('sku'))->shouldBeCalled();
        $webservice->updateProductPart(Argument::any())->shouldBeCalled();
        $webservice->sendImages(Argument::any())->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('Products sent')->shouldBeCalled();
        $stepExecution->incrementSummaryInfo('Products images sent')->shouldBeCalledTimes(2);

        $this->write($products);
    }

    function it_fails_if_something_went_wrong_when_it_updates_a_product($webservice, $stepExecution)
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

        $webservice->getImages('sku', 'default')->willReturn(array());
        $webservice->sendProduct(array('sku'))->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');
        $webservice->sendImages(Argument::any())->shouldNotBeCalled();
        $webservice->updateProductPart(Argument::any())->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('Products sent')->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('Products images sent')->shouldNotBeCalled();

        $this->shouldThrow('\Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->duringWrite($products);
    }

    function it_fails_if_something_went_wrong_when_it_prunes_images($webservice, $stepExecution)
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

        $webservice->getImages('sku', 'default')->willReturn(array(array('file' => 'foo'), array('file' => 'bar')));
        $webservice->deleteImage('sku','foo')->willThrow('\Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException');
        $webservice->sendProduct(Argument::any())->shouldNotBeCalled();
        $webservice->sendImages(Argument::any())->shouldNotBeCalled();
        $webservice->updateProductPart(Argument::any())->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('Products sent')->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('Products images sent')->shouldNotBeCalled();

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
