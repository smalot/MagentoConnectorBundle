<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Manager\SimpleMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\FamilyManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento family mapper
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMFamilyMapper extends ORMMapper
{
    /**
     * @var FamilyManager
     */
    protected $familyManager;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param SimpleMappingManager         $simpleMappingManager
     * @param string                       $rootIdentifier
     * @param FamilyManager                $familyManager
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        SimpleMappingManager         $simpleMappingManager,
        FamilyManager                $familyManager,
        $rootIdentifier
    ) {
        parent::__construct($hasValidCredentialsValidator, $simpleMappingManager, $rootIdentifier);

        $this->familyManager = $familyManager;
    }

    /**
     * Get all targets
     * @return array
     */
    public function getAllTargets()
    {
        $targets = array();

        if ($this->isValid()) {
            $families = $this->familyManager->getFamilies();

            foreach ($families as $family) {
                $targets[] = array('id' => $family->getCode(), 'name' => $family->getCode());
            }
        }

        return $targets;
    }
}
