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
        $configuration = $manager->getMagentoConfigurationByCode($code);
        $translator    = $this->getTranslator();

        $translator->setLocale($this->getDefaultLocale());
        $violations = $validator->validate($configuration, ['connection']);

        if ($violations->count() !== 0) {
            foreach ($violations as $violation) {
                $output->writeln($translator->trans($violation->getMessage()));
                foreach ($violation->getMessageParameters() as $error) {
                    $output->writeln(sprintf('ERROR : "%s"', $error));
                }
                foreach ($violation->getInvalidValue() as $key => $value) {
                    $output->writeln(sprintf('INVALID VALUE %s : "%s"', $key, $value));
                }
            }
        } else {
            $output->writeln(sprintf('Connection to Magento is OK with %s configuration.', $code));
        }
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

    /**
     * Returns the default locale
     *
     * @return string
     */
    protected function getDefaultLocale()
    {
        return $this->getContainer()->getParameter('locale');
    }
}
