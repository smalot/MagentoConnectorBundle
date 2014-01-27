<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\WebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Magento category cleaner
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class CategoryCleaner extends Cleaner
{
    /**
     * @var CategoryMappingManager
     */
    protected $categoryMappingManager;

    /**
     * @param WebserviceGuesser      $webserviceGuesser
     * @param CategoryMappingManager $categoryMappingManager
     */
    public function __construct(
        WebserviceGuesser $webserviceGuesser,
        CategoryMappingManager $categoryMappingManager
    ) {
        parent::__construct($webserviceGuesser);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    public function execute()
    {
        parent::beforeExecute();

        $magentoCategories = $this->webservice->getCategoriesStatus();

        foreach ($magentoCategories as $category) {
            if (!$this->categoryMappingManager->magentoCategoryExist($category['category_id'], $this->soapUrl) &&
                !(
                    $category['level'] === '0' ||
                    $category['level'] === '1'
                )
            ) {
                try {
                    $this->handleCategoryNotInPimAnymore($category);
                } catch (SoapCallException $e) {
                    throw new InvalidItemException($e->getMessage(), array(json_encode($category)));
                }
            }
        }
    }

    protected function handleCategoryNotInPimAnymore(array $category)
    {
        if ($this->notInPimAnymoreAction === self::DISABLE) {
            $this->webservice->disableCategory($category['category_id']);
        } elseif ($this->notInPimAnymoreAction === self::DELETE) {
            $this->webservice->deleteCategory($category['category_id']);
        }
    }
}
