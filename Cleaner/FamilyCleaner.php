<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
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
     * @param WebserviceGuesser    $webserviceGuesser
     * @param FamilyMappingManager $familyMappingManager
     */
    public function __construct(
        WebserviceGuesser    $webserviceGuesser,
        FamilyMappingManager $familyMappingManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->familyMappingManager = $familyMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoFamilies = $this->webservice->getAttributeSetList();

        foreach ($magentoFamilies as $family) {
            try {
                $this->handleFamilyNotInPimAnymore($family);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array(json_encode($family)));
            }
        }
    }

    /**
     * Handle deletion of families that are not in PIM anymore
     * @param array $family
     */
    protected function handleFamilyNotInPimAnymore($family)
    {
        try {
            if (!$this->familyMappingManager->magentoFamilyExists($family, $this->getSoapUrl())) {
                $this->webservice->removeAttributeSet($family);
                $this->stepExecution->incrementSummaryInfo(self::FAMILY_DELETED);
            }
        } catch (SoapCallException $e) {
            var_dump($e->getMessage());
        }
    }

    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'notInPimAnymoreAction' => array(
                    'type'    => 'choice',
                    'options' => array(
                        'choices'  => array(
                            Cleaner::DO_NOTHING => 'pim_magento_connector.export.do_nothing.label',
                            Cleaner::DELETE     => 'pim_magento_connector.export.delete.label'
                        ),
                        'required' => true,
                        'help'     => 'pim_magento_connector.export.notInPimAnymoreAction.help',
                        'label'    => 'pim_magento_connector.export.notInPimAnymoreAction.label'
                    )
                )
            )
        );
    }
}
