<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Magento group cleaner
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class GroupCleaner extends Cleaner
{
    const GROUP_DELETED  = 'Group deleted';

    /**
     * @var GroupMappingManager
     */
    protected $groupMappingManager;

    /**
     * @param WebserviceGuesser    $webserviceGuesser
     * @param GroupMappingManager $groupMappingManager
     */
    public function __construct(
        WebserviceGuesser    $webserviceGuesser,
        GroupMappingManager $groupMappingManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->groupMappingManager = $groupMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoGroups = $this->webservice->getAttributeSetList();

        foreach ($magentoGroups as $group) {
            try {
                $this->handleGroupNotInPimAnymore($group);
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array(json_encode($group)));
            }
        }
    }

    /**
     * Handle deletion of groups that are not in PIM anymore
     * @param array $group
     */
    protected function handleGroupNotInPimAnymore($group)
    {
        try {
            if (!$this->groupMappingManager->magentoGroupExists($group, $this->getSoapUrl())) {
                $this->webservice->removeAttributeGroupFromAttributeSet($group);
                $this->groupMappingManager->removeGroupFromMapping($group->getId(), $this->getSoapUrl());
                $this->stepExecution->incrementSummaryInfo(self::GROUP_DELETED);
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
