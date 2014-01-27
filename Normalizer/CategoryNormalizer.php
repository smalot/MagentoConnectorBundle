<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;
use Pim\Bundle\MagentoConnectorBundle\Webservice\Webservice;

/**
 * A normalizer to transform a category entity into an array
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer extends AbstractNormalizer
{
    /**
     * @var CategoryMappingManager
     */
    protected $categoryMappingManager;

    /**
     * @param ChannelManager         $channelManager
     * @param CategoryMappingManager $categoryMappingManager
     */
    public function __construct(
        ChannelManager $channelManager,
        CategoryMappingManager $categoryMappingManager
    ) {
        parent::__construct($channelManager);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $normalizedCategory = $this->getDefaultCategory($object, $context);

        //For each storeview, we update the product only with localized attributes
        foreach ($this->getPimLocales($context['channel']) as $locale) {
            $storeViewCode = $this->getStoreViewCodeForLocale(
                $locale->getCode(),
                $context['magentoStoreViews'],
                $context['storeViewMapping']
            );

            //If a locale for this storeview exist in PIM, we create a translated product in this locale
            if ($storeViewCode) {
                $normalizedCategory['variation'][] = $this->getNormalizedVariationCategory(
                    $object,
                    $locale->getCode(),
                    $storeViewCode
                );
            }
        }

        return $normalizedCategory;
    }

    /**
     * Get the default category
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return array
     */
    protected function getDefaultCategory(CategoryInterface $category, array $context)
    {
        $normalizedCategory = array(
            'create'    => array(),
            'update'    => array(),
            'move'      => array(),
            'variation' => array()
        );

        if ($this->magentoCategoryExists($category, $context['magentoCategories'], $context['magentoUrl'])) {
            $normalizedCategory['update'][] = $this->getNormalizedUpdateCategory(
                $category,
                $context
            );

            if ($this->categoryHasMoved($category, $context['magentoCategories'], $context['magentoUrl'])) {
                $normalizedCategory['move'][] = $this->getNormalizedMoveCategory($category, $context);
            }
        } else {
            $normalizedCategory['create'][] = $this->getNormalizedNewCategory($category, $context);
        }

        return $normalizedCategory;
    }

    /**
     * Test if the given category exist on Magento side
     * @param CategoryInterface $category
     * @param array             $magentoCategories
     * @param string            $magentoUrl
     *
     * @return boolean
     */
    protected function magentoCategoryExists(CategoryInterface $category, array $magentoCategories, $magentoUrl)
    {
        return ($magentoCategoryId = $this->getMagentoCategoryId($category, $magentoUrl)) !== null &&
            isset($magentoCategories[$magentoCategoryId]);
    }

    /**
     * Get category id on Magento side for the given category
     * @param CategoryInterface $category
     * @param string            $magentoUrl
     *
     * @return int
     */
    protected function getMagentoCategoryId(CategoryInterface $category, $magentoUrl)
    {
        return $this->categoryMappingManager->getIdFromCategory($category, $magentoUrl);
    }

    /**
     * Get new normalized categories
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return array
     */
    protected function getNormalizedNewCategory(CategoryInterface $category, array $context)
    {
        return array(
            'magentoCategory' => array(
                (string) $this->categoryMappingManager->getIdFromCategory(
                    $category->getParent(),
                    $context['magentoUrl'],
                    $context['rootCategoryMapping']
                ),
                array(
                    'name'              => $this->getCategoryLabel($category, $context['defaultLocale']),
                    'is_active'         => 1,
                    'include_in_menu'   => 1,
                    'available_sort_by' => 1,
                    'default_sort_by'   => 1
                ),
                Webservice::SOAP_DEFAULT_STORE_VIEW
            ),
            'pimCategory' => $category
        );
    }

    /**
     * Get update normalized categories
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return array
     */
    protected function getNormalizedUpdateCategory(CategoryInterface $category, array $context)
    {
        return array(
            $this->getMagentoCategoryId($category, $context['magentoUrl']),
            array(
                'name'              => $this->getCategoryLabel($category, $context['defaultLocale']),
                'available_sort_by' => 1,
                'default_sort_by'   => 1
            ),
            Webservice::SOAP_DEFAULT_STORE_VIEW
        );
    }

    /**
     * Get normalized variation category
     * @param CategoryInterface $category
     * @param string            $localeCode
     * @param string            $storeViewCode
     *
     * @return array
     */
    protected function getNormalizedVariationCategory(CategoryInterface $category, $localeCode, $storeViewCode)
    {
        return array(
            'magentoCategory' => array(
                null,
                array(
                    'name'              => $this->getCategoryLabel($category, $localeCode),
                    'available_sort_by' => 1,
                    'default_sort_by'   => 1
                ),
                $storeViewCode
            ),
            'pimCategory' => $category,
        );
    }

    /**
     * Get move normalized categories
     * @param CategoryInterface $category
     * @param array             $context
     *
     * @return array
     */
    protected function getNormalizedMoveCategory(CategoryInterface $category, array $context)
    {
        return array(
            $this->getMagentoCategoryId($category, $context['magentoUrl']),
            $this->categoryMappingManager->getIdFromCategory(
                $category->getParent(),
                $context['magentoUrl'],
                $context['rootCategoryMapping']
            )
        );
    }

    /**
     * Get category label
     * @param CategoryInterface $category
     * @param string            $localeCode
     *
     * @return string
     */
    protected function getCategoryLabel(CategoryInterface $category, $localeCode)
    {
        $category->setLocale($localeCode);

        return $category->getLabel();
    }

    /**
     * Test if the category has moved on magento side
     * @param CategoryInterface $category
     * @param array             $magentoCategories
     * @param string            $magentoUrl
     *
     * @return boolean
     */
    protected function categoryHasMoved(CategoryInterface $category, array $magentoCategories, $magentoUrl)
    {
        $currentCategoryId = $this->getMagentoCategoryId($category, $magentoUrl);
        $currentParentId   = $this->getMagentoCategoryId($category->getParent(), $magentoUrl);

        return $magentoCategories[$currentCategoryId] !== $currentParentId;
    }
}
