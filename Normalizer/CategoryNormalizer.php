<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Category;
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
     * @param ChannelManager           $channelManager
     * @param WebserviceGuesser        $webserviceGuesser
     * @param ProductNormalizerGuesser $normalizerGuesser
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

    protected function getDefaultCategory(Category $category, array $context)
    {
        $normalizedCategory = array(
            'create'    => array(),
            'update'    => array(),
            'move'      => array(),
            'variation' => array()
        );

        if ($this->magentoCategoryExist($category, $context['magentoCategories'], $context['magentoUrl'])) {
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
     * @param Category $category
     * @param array    $magentoCategories
     *
     * @return boolean
     */
    protected function magentoCategoryExist(Category $category, array $magentoCategories, $magentoUrl)
    {
        if (($magentoCategoryId = $this->getMagentoCategoryId($category, $magentoUrl)) !== null &&
            isset($magentoCategories[$magentoCategoryId])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get category id on Magento side for the given category
     * @param Category $category
     * @param string   $magentoUrl
     *
     * @return int
     */
    protected function getMagentoCategoryId(Category $category, $magentoUrl)
    {
        return $this->categoryMappingManager->getIdFromCategory($category, $magentoUrl);
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
                (string) $this->getMagentoParentId($category, $context['rootCategoryMapping'], $context['magentoUrl']),
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
     * @param Category $category
     * @param array    $context
     *
     * @return array
     */
    protected function getNormalizedUpdateCategory(Category $category, array $context)
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

    protected function getNormalizedVariationCategory(Category $category, $locale, $storeViewCode)
    {
        return array(
            'magentoCategory' => array(
                null,
                array(
                    'name'              => $this->getCategoryLabel($category, $locale),
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
     * @param Category $category
     * @param array    $context
     *
     * @return array
     */
    protected function getNormalizedMoveCategory(Category $category, array $context)
    {
        return array(
            $this->getMagentoCategoryId($category, $context['magentoUrl']),
            $this->getMagentoParentId($category, $context['rootCategoryMapping'], $context['magentoUrl'])
        );
    }

    protected function getCategoryLabel(Category $category, $locale)
    {
        $category->setLocale($locale);

        return $category->getLabel();
    }

    /**
     * Get Magento parent id
     * @param Category $category
     *
     * @return int
     */
    protected function getMagentoParentId(Category $category, $rootCategoryMapping, $magentoUrl)
    {
        if (isset($rootCategoryMapping[$category->getParent()->getCode()])) {
            return $rootCategoryMapping[$category->getParent()->getCode()];
        } else {
            return $this->getMagentoCategoryId($category->getParent(), $magentoUrl);
        }
    }

    /**
     * Test if the category has moved on magento side
     * @param  Category $category
     * @param  array    $magentoCategories
     * @return [type]
     */
    protected function categoryHasMoved(Category $category, $magentoCategories, $magentoUrl)
    {
        $currentCategoryId = $this->getMagentoCategoryId($category, $magentoUrl);
        $currentParentId   = $this->getMagentoCategoryId($category->getParent(), $magentoUrl);

        if ($magentoCategories[$currentCategoryId] !== $currentParentId) {
            return true;
        } else {
            return false;
        }
    }
}
