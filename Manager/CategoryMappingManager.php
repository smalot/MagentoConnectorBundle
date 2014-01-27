<?php

namespace Pim\Bundle\MagentoConnectorBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Entity\Category;

/**
 * Category mapping manager
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryMappingManager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * Constructor
     * @param ObjectManager $objectManager
     * @param string        $className
     */
    public function __construct(ObjectManager $objectManager, $className)
    {
        $this->objectManager = $objectManager;
        $this->className     = $className;
    }

    /**
     * Get category from id and Magento url
     * @param int    $id
     * @param string $magentoUrl
     *
     * @return Category
     */
    public function getCategoryFromId($id, $magentoUrl)
    {
        $magentoCategoryMapping = $this->getEntityRepository()->findOneBy(
            array(
                'magentoCategoryId' => $id,
                'magentoUrl'        => $magentoUrl
            )
        );

        return $magentoCategoryMapping ? $magentoCategoryMapping->getCategory() : null;
    }

    /**
     * Get id from category and Magento url
     * @param Category $category
     * @param string   $magentoUrl
     * @param array    $rootCategoryMapping
     *
     * @return int
     */
    public function getIdFromCategory(Category $category, $magentoUrl, array $rootCategoryMapping = array())
    {
        if (isset($rootCategoryMapping[$category->getCode()])) {
            return $rootCategoryMapping[$category->getCode()];
        } else {
            $categoryMapping = $this->getEntityRepository()->findOneBy(
                array(
                    'category'   => $category,
                    'magentoUrl' => $magentoUrl
                )
            );

            return $categoryMapping ? $categoryMapping->getMagentoCategoryId() : null;
        }
    }

    /**
     * Register a new category mapping
     * @param Category $pimCategory
     * @param int      $magentoCategoryId
     * @param string   $magentoUrl
     */
    public function registerCategoryMapping(
        Category $pimCategory,
        $magentoCategoryId,
        $magentoUrl
    ) {
        $magentoCategoryMapping = new $this->className();
        $magentoCategoryMapping->setCategory($pimCategory);
        $magentoCategoryMapping->setMagentoCategoryId($magentoCategoryId);
        $magentoCategoryMapping->setMagentoUrl($magentoUrl);

        $this->objectManager->persist($magentoCategoryMapping);
        $this->objectManager->flush();
    }

    /**
     * Does the given magento category exist in pim ?
     * @param string $categoryId
     * @param string $magentoUrl
     *
     * @return boolean
     */
    public function magentoCategoryExists($categoryId, $magentoUrl)
    {
        return $this->getCategoryFromId($categoryId, $magentoUrl) !== null;
    }

    /**
     * Get the entity manager
     * @return EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->objectManager->getRepository($this->className);
    }
}
