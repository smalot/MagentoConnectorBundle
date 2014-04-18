<?php

namespace Pim\Bundle\MagentoConnectorBundle\Cleaner;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\webserviceGuesserFactory;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

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
    const CATEGORY_DELETED  = 'Category deleted';
    const CATEGORY_DISABLED = 'Category disabled';

    /**
     * @var CategoryMappingManager
     */
    protected $categoryMappingManager;

    /**
     * @param webserviceGuesserFactory      $webserviceGuesserFactory
     * @param CategoryMappingManager        $categoryMappingManager
     */
    public function __construct(
        webserviceGuesserFactory $webserviceGuesserFactory,
        CategoryMappingManager $categoryMappingManager
    ) {
        parent::__construct($webserviceGuesserFactory);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $magentoCategories = $this->webserviceGuesserFactory
            ->getWebservice('category', $this->getClientParameters())->getCategoriesStatus();

        foreach ($magentoCategories as $category) {
            if (!$this->categoryMappingManager->magentoCategoryExists($category['category_id'], $this->soapUrl) &&
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

    /**
     * Handle deletion or disableing of categories that are not in PIM anymore
     * @param array $category
     */
    protected function handleCategoryNotInPimAnymore(array $category)
    {
        if ($this->notInPimAnymoreAction === self::DISABLE) {
            $this->webserviceGuesserFactory
                ->getWebservice('category', $this->getClientParameters())->disableCategory($category['category_id']);
            $this->stepExecution->incrementSummaryInfo(self::CATEGORY_DISABLED);
        } elseif ($this->notInPimAnymoreAction === self::DELETE) {
            try {
                $this->webserviceGuesserFactory
                    ->getWebservice('category', $this->getClientParameters())->deleteCategory($category['category_id']);
                $this->stepExecution->incrementSummaryInfo(self::CATEGORY_DELETED);
            } catch (SoapCallException $e) {
                //In any case, if deleteCategory fails, it is due to the parent category has allready been deleted.
            }
        }
    }
}
