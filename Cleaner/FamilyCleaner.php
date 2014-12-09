<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Webservice\MagentoSoapClientParametersRegistry;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Magento family cleaner
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class FamilyCleaner extends Cleaner
{
    const FAMILY_DELETED  = 'Family deleted';

    /**
     * @var FamilyMappingManager
     */
    protected $familyMappingManager;

    /**
     * @param WebserviceGuesser                   $webserviceGuesser
     * @param FamilyMappingManager                $familyMappingManager
     * @param MagentoSoapClientParametersRegistry $clientParametersRegistry
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        FamilyMappingManager $familyMappingManager,
        MagentoSoapClientParametersRegistry $clientParametersRegistry
    ) {
        parent::__construct($webserviceGuesser, $clientParametersRegistry);

        $this->familyMappingManager = $familyMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoFamilies = $this->webservice->getAttributeSetList();

        foreach ($magentoFamilies as $name => $id) {
            try {
                $this->handleFamilyNotInPimAnymore($name, $id);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), [$name]);
            }
        }
    }

    /**
     * Handle deletion of families that are not in PIM anymore
     *
     * @param string $name
     * @param int    $id
     */
    protected function handleFamilyNotInPimAnymore($name, $id)
    {
        if (!$this->familyMappingManager->magentoFamilyExists($id, $this->getSoapUrl())
            && !in_array($name, $this->getIgnoredFamilies())) {
            $this->webservice->removeAttributeSet($id);
            $this->stepExecution->incrementSummaryInfo(self::FAMILY_DELETED);
        }
    }

    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            [
                'notInPimAnymoreAction' => [
                    'type'    => 'choice',
                    'options' => [
                        'choices'  => [
                            Cleaner::DO_NOTHING => 'pim_magento_connector.export.do_nothing.label',
                            Cleaner::DELETE     => 'pim_magento_connector.export.delete.label',
                        ],
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.notInPimAnymoreAction.help',
                        'label'    => 'pim_magento_connector.export.notInPimAnymoreAction.label',
                        'attr'     => ['class' => 'select2'],
                    ],
                ]
            ]
        );
    }

    /**
     * Get all ignored families
     *
     * @return string[]
     */
    protected function getIgnoredFamilies()
    {
        return [
            'Default',
        ];
    }
}
