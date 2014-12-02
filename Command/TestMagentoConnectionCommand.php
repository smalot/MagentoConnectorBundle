<?php

namespace Pim\Bundle\MagentoConnectorBundle\Command;

use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoConfigurationManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator;
use Oro\Bundle\TranslationBundle\Translation\Translator;

/**
 * This command allows to check validity of a MagentoConfiguration
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TestMagentoConnectionCommand extends ContainerAwareCommand
{
    /** @staticvar int */
    const SUCCESS = 0;

    /** @staticvar int */
    const CONFIGURATION_NOT_FOUND = 1;

    /** @staticvar int */
    const VALIDATION_ERROR = 2;

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
     *
     * @return int 0: success; 1: Configuration not found; 2: Error during validation;
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $validator     = $this->getValidator();
        $code          = $input->getArgument('configuration_code');
        $manager       = $this->getMagentoConfigurationManager();
        $configuration = $manager->getMagentoConfigurationByCode($code);
        $translator    = $this->getTranslator();

        if (null === $configuration) {
            $output->writeln(
                sprintf('<error>Given configuration with code "%s" does not exist.</error>', $code)
            );
            $status = static::CONFIGURATION_NOT_FOUND;
        } else {
            $translator->setLocale('en');
            $violations = $validator->validate($configuration, ['connection']);

            if ($violations->count() !== 0) {
                foreach ($violations as $violation) {
                    if (null !== $violation->getCode()) {
                        $output->writeln(sprintf('<error>CODE "%s"</error>', $violation->getCode()));
                    }
                    $output->writeln(sprintf('<error>%s</error>', $translator->trans($violation->getMessage())));
                }

                $output->writeln(sprintf('<comment>%s</comment>', $configuration));
                $status = static::VALIDATION_ERROR;
            } else {
                $output->writeln(sprintf('<info>Connection to Magento is OK with "%s" configuration.</info>', $code));
                $status = static::SUCCESS;
            }
        }

        return $status;
    }

    /**
     * Returns the Magento configuration manager
     *
     * @return MagentoConfigurationManager
     */
    protected function getMagentoConfigurationManager()
    {
        return $this->getContainer()->get('pim_magento_connector.manager.magento_configuration');
    }

    /**
     * Returns the symfony validator
     *
     * @return Validator
     */
    protected function getValidator()
    {
        return $this->getContainer()->get('validator');
    }

    /**
     * Returns the symfony translator
     *
     * @return Translator
     */
    protected function getTranslator()
    {
        return $this->getContainer()->get('translator');
    }
}
