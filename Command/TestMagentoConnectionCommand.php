<?php

namespace Pim\Bundle\MagentoConnectorBundle\Command;

use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator;

class TestMagentoConnectionCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('magento-connector:connection:test')
            ->setDescription('Tests the connection between connector and Magento with the given configuration.')
            ->addArgument('configuration_code', InputArgument::REQUIRED, 'Magento configuration code');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validator     = $this->getValidator();
        $code          = $input->getArgument('configuration_code');
        $manager       = $this->getMagentoConfigurationManager();
        $configuration = $manager->getRepository()->findOneBy(['code' => $code]);

        $errors = $validator->validate($configuration, ['connection']); //, ['connection']

        die(var_dump($errors));

        $output->writeln('');
    }

    /**
     * Returns the Magento configuration manager
     *
     * @return MagentoConfigurationManager
     */
    protected function getMagentoConfigurationManager()
    {
        return $this
            ->getContainer()
            ->get('pim_magento_connector.manager.magento_configuration');
    }

    /**
     * Returns the symfony validator
     *
     * @return Validator
     */
    protected function getValidator()
    {
        return $this
            ->getContainer()
            ->get('validator');
    }
}
