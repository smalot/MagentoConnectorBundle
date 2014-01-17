<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoWebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoNormalizerGuesser;

/**
 * Magento category processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryMagentoProcessor extends AbstractMagentoProcessor
{
    /**
     * @var CategoryMappingManager
     */
    protected $categoryMappingManager;

    /**
     * @var string
     */
    protected $rootCategoryMapping = '';

    /**
     * get rootCategoryMapping
     *
     * @return string rootCategoryMapping
     */
    public function getRootCategoryMapping()
    {
        return $this->rootCategoryMapping;
    }

    /**
     * Set rootCategoryMapping
     *
     * @param string $rootCategoryMapping rootCategoryMapping
     *
     * @return AbstractMagentoProcessor
     */
    public function setRootCategoryMapping($rootCategoryMapping)
    {
        $this->rootCategoryMapping = $rootCategoryMapping;

        return $this;
    }

    /**
     * Get computed storeView mapping (string to array)
     * @return array
     */
    protected function getComputedRootCategoryMapping()
    {
        return $this->getComputedMapping($this->rootCategoryMapping);
    }

    /**
     * @param ChannelManager           $channelManager
     * @param MagentoWebserviceGuesser $magentoWebserviceGuesser
     * @param ProductNormalizerGuesser $magentoNormalizerGuesser
     */
    public function __construct(
        ChannelManager $channelManager,
        MagentoWebserviceGuesser $magentoWebserviceGuesser,
        MagentoNormalizerGuesser $magentoNormalizerGuesser,
        CategoryMappingManager $categoryMappingManager
    ) {
        parent::__construct($channelManager, $magentoWebserviceGuesser, $magentoNormalizerGuesser);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    /**
     * Function called before all process
     */
    protected function beforeProcess()
    {
        $this->magentoWebservice = $this->magentoWebserviceGuesser->getWebservice($this->getClientParameters());

        $magentoCategories = $this->magentoWebservice->getCategoriesStatus();

        $this->globalContext = array(
            'magentoCategories' => $magentoCategories,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($categories)
    {
        $this->beforeProcess();

        $normalizedCategories = array('create' => array(), 'update' => array(), 'move' => array());

        $categories = is_array($categories) ? $categories : array($categories);

        foreach ($categories as $category) {
            if ($category->getParent()) {
                $context = $this->globalContext;

                if ($this->magentoCategoryExist($category, $this->globalContext['magentoCategories'])) {
                    $normalizedCategories['update'][] = $this->getNormalizedUpdateCategory($category, $context);

                    if ($this->categoryHasMoved($category, $this->globalContext['magentoCategories'])) {
                        $normalizedCategories['move'][] = $this->getNormalizedMoveCategory($category, $context);
                    }
                } else {
                    $normalizedCategories['create'][] = $this->getNormalizedNewCategory($category, $context);
                }

            }
        }

        return $normalizedCategories;
    }

    /**
     * Get new normalized categories
     * @param Category $category
     * @param array    $context
     *
     * @return array
     */
    protected function getNormalizedNewCategory(Category $category, array $context)
    {
        return array(
            'magentoCategory' => array(
                (string) $this->getParentId($category),
                array(
                    'name'              => $category->getCode(),
                    'is_active'         => 1,
                    'include_in_menu'   => 1,
                    'available_sort_by' => 1,
                    'default_sort_by'   => 1
                ),
                'default'
            ),
            'pimCategory' => $category
        );
    }

    /**
     * Get update normalized categories
     * @param Category $category
     * @param array    $context
     *
     * @return array
     */
    protected function getNormalizedUpdateCategory(Category $category, array $context)
    {
        return array(
            $this->getMagentoCategoryId($category),
            array(
                'name'              => $category->getCode() . 'test',
                'is_active'         => 1,
                'include_in_menu'   => 1,
                'available_sort_by' => 1,
                'default_sort_by'   => 1
            )
        );
    }

    /**
     * Get move normalized categories
     * @param Category $category
     * @param array    $context
     *
     * @return array
     */
    protected function getNormalizedMoveCategory(Category $category, array $context)
    {
        return array(
            $this->getMagentoCategoryId($category),
            $this->getParentId($category)
        );
    }

    protected function getParentId(Category $category)
    {
        $rootCategoryMapping = $this->getComputedRootCategoryMapping();

        if (isset($rootCategoryMapping[$category->getParent()->getCode()])) {
            return $rootCategoryMapping[$category->getParent()->getCode()];
        } else {
            return $this->getMagentoCategoryId($category->getParent());
        }
    }

    protected function magentoCategoryExist(Category $category, $magentoCategories)
    {
        if (($magentoCategoryId = $this->getMagentoCategoryId($category)) !== null &&
            isset($magentoCategories[$magentoCategoryId])
        ) {
            return true;
        } else {
            return false;
        }
    }

    protected function getMagentoCategoryId(Category $category)
    {
        return $this->categoryMappingManager->getIdFromCategory($category, $this->soapUrl);
    }

    protected function categoryHasMoved(Category $category, $magentoCategories)
    {
        $currentCategoryId = $this->getMagentoCategoryId($category);
        $currentParentId   = $this->getMagentoCategoryId($category->getParent());

        if ($magentoCategories[$currentCategoryId] !== $currentParentId) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array_merge(
            parent::getConfigurationFields(),
            array(
                'rootCategoryMapping' => array(
                    'type'    => 'textarea',
                    'options' => array(
                        'required' => false
                    )
                )
            )
        );
    }
}
