<?php

namespace Pim\Bundle\MagentoConnectorBundle\Twig;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class ConfigurationExtension extends \Twig_Extension
{
    public function getGlobals()
    {
        $yaml = new Parser();

        try {
            $configuration = $yaml->parse(file_get_contents(__DIR__.'/../Resources/config/configuration_settings.yml'));
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
            $configuration = array();
        }

        $configuration['show_configuration'] = isset($configuration['show_configuration']) ?
            $configuration['show_configuration'] : array();
        $configuration['edit_configuration'] = isset($configuration['edit_configuration']) ?
            $configuration['edit_configuration'] : array();

        return $configuration;
    }

    public function getName()
    {
        return 'pim_magento_connector_extension';
    }
}
