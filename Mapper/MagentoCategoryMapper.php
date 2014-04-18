<?php

namespace Pim\Bundle\MagentoConnectorBundle\Mapper;

use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentialsValidator;

/**
 * Magento category mapper
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoCategoryMapper extends Mapper
{
    const ROOT_CATEGORY_ID = 1;

    /**
     * @var WebserviceGuesserFactory
     */
    protected $webserviceGuesserFactory;

    /**
     * @param HasValidCredentialsValidator $hasValidCredentialsValidator
     * @param WebserviceGuesserFactory     $webserviceGuesserFactory
     */
    public function __construct(
        HasValidCredentialsValidator $hasValidCredentialsValidator,
        WebserviceGuesserFactory $webserviceGuesserFactory
    ) {
        parent::__construct($hasValidCredentialsValidator);

        $this->webserviceGuesserFactory = $webserviceGuesserFactory;
    }

    /**
     * Get all targets
     * @return array
     */
    public function getAllTargets()
    {
        $targets = array();

        if ($this->isValid()) {
            $categories = $this->webserviceGuesserFactory
                ->getWebservice('category', $this->getClientParameters())->getCategoriesStatus();

            foreach ($categories as $categoryId => $category) {
                if ($categoryId != self::ROOT_CATEGORY_ID) {
                    $targets[] = array('id' => $categoryId, 'text' => $category['name']);
                }
            }
        }

        return $targets;
    }

    /**
     * Get mapper identifier
     * @param string $rootIdentifier
     *
     * @return string
     */
    public function getIdentifier($rootIdentifier = 'category')
    {
        return parent::getIdentifier($rootIdentifier);
    }
}
