<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\GroupMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\MagentoGroupManager;
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
     * @var MagentoGroupManager
     */
    protected $magentoGroupManager;

    /**
     * @param WebserviceGuesser   $webserviceGuesser
     * @param MagentoGroupManager $magentoGroupManager
     * @param GroupMappingManager $groupMappingManager
     */
    public function __construct(
        WebserviceGuesser    $webserviceGuesser,
        MagentoGroupManager  $magentoGroupManager,
        GroupMappingManager  $groupMappingManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->magentoGroupManager = $magentoGroupManager;
        $this->groupMappingManager = $groupMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        parent::beforeExecute();

        $magentoGroups = $this->magentoGroupManager->getAllMagentoGroups();

        foreach ($magentoGroups as $group) {
            try {
                $this->handleGroupNotInPimAnymore($group->getMagentoGroupId());
            } catch (SoapCallException $e) {
                throw new InvalidItemException($e->getMessage(), array(json_encode($group)));
            }
        }
    }

    /**
     * Handle deletion of groups that are not in PIM anymore
     * @param int $groupId
     */
    protected function handleGroupNotInPimAnymore($groupId)
    {
        try {
            if (!$this->groupMappingManager->magentoGroupExists($groupId, $this->getSoapUrl())
            && !in_array($groupId, $this->getIgnoredCleaner())
            ) {
                $this->webservice->removeAttributeGroupFromAttributeSet($groupId);
                $this->magentoGroupManager->removeMagentoGroup($groupId, $this->getSoapUrl());
                $this->stepExecution->incrementSummaryInfo(self::GROUP_DELETED);
            }
        } catch (SoapCallException $e) {
            var_dump($e->getMessage());
        }
    }

    /**
     * Get all ignored cleaners
     * @return array
     */
    protected function getIgnoredCleaner()
    {
        return array(
            'Default',
        );
    }
}
