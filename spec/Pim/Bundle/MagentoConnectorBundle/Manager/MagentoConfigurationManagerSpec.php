<?php

namespace spec\Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration;
use Pim\Bundle\MagentoConnectorBundle\Entity\Repository\MagentoConfigurationRepository;
use Pim\Bundle\MagentoConnectorBundle\Factory\MagentoSoapClientFactory;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClient;
use Prophecy\Argument;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MagentoConfigurationManagerSpec extends ObjectBehavior
{
    function let(RegistryInterface $doctrine, MagentoSoapClientFactory $clientFactory)
    {
        $magentoConfigClass = 'Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration';

        $this->beConstructedWith($doctrine, $clientFactory, $magentoConfigClass);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager');
    }

    function it_returns_the_magento_configuration_repository(MagentoConfigurationRepository $repo, $doctrine)
    {
        $doctrine->getRepository('Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration')->willReturn($repo);

        $this->getRepository()->shouldReturn($repo);
    }

    function it_saves_a_magento_configuration(MagentoConfiguration $configuration, EntityManager $em, $doctrine)
    {
        $doctrine->getEntityManager()->willReturn($em);
        $em->persist($configuration)->shouldBeCalled();
        $em->flush($configuration)->shouldBeCalled();

        $this->save($configuration)->shouldReturn($this);
    }

    function it_finds_a_magento_configuration_by_its_code_and_returns_it(
        MagentoConfiguration $configuration,
        MagentoConfigurationRepository $repo,
        $doctrine
    ) {
        $doctrine->getRepository('Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration')->willReturn($repo);

        $repo->findOneBy(['code' => 'config_1'])->willReturn($configuration);

        $this->getMagentoConfigurationByCode('config_1')->shouldReturn($configuration);
    }

    function it_returns_null_if_no_magento_configuration_is_find_by_code(
        MagentoConfigurationRepository $repo,
        $doctrine
    ) {
        $doctrine->getRepository('Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration')->willReturn($repo);

        $repo->findOneBy(['code' => 'config_1'])->willReturn(null);

        $this->getMagentoConfigurationByCode('config_1')->shouldReturn(null);
    }

    function it_returns_configuration_choices(
        MagentoConfigurationRepository $repo,
        $doctrine
    ) {
        $doctrine->getRepository('Pim\Bundle\MagentoConnectorBundle\Entity\MagentoConfiguration')->willReturn($repo);

        $repo->getChoices()->willReturn(
            [
                ['code' => 'choice_code_1', 'label' => 'choice_label_1'],
                ['code' => 'choice_code_2', 'label' => 'choice_label_2']
            ]
        );

        $this->getConfigurationChoices()->shouldReturn([
            'choice_code_1' => 'choice_label_1',
            'choice_code_2' => 'choice_label_2'
        ]);
    }

    function it_creates_magento_soap_client(
        MagentoConfiguration $configuration,
        MagentoSoapClient $magSoapClient,
        $clientFactory
    ) {
        $clientFactory->createMagentoSoapClient($configuration, [])->willReturn($magSoapClient);

        $this->createClient($configuration, [])->shouldReturn($magSoapClient);
    }
}
