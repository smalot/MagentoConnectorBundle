<?php

namespace Pim\Bundle\MagentoConnectorBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\CategoryLabelDictionary;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a category to the api import format
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryNormalizer implements NormalizerInterface
{
    /** @var CategoryRepository */
    protected $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $defaultLocale = $context['defaultLocale'];
        $categoryTree  = $this->getTranslatedCategoryTree($object, $defaultLocale);
        $rootName      = $this->getRootName($categoryTree, $context['userCategoryMapping']);
        $categoryPath  = $this->getCategoryPath($categoryTree);
        $categoryName  = $this->getTranslatedName($object, $defaultLocale);

        $normalized = [
            CategoryLabelDictionary::ROOT_HEADER            => $rootName,
            CategoryLabelDictionary::NAME_HEADER            => $categoryName,
            CategoryLabelDictionary::CATEGORY_HEADER        => $categoryPath,
            CategoryLabelDictionary::ACTIVE_HEADER          => 'yes',
            CategoryLabelDictionary::POSITION_HEADER        => $object->getLeft(),
            CategoryLabelDictionary::INCLUDE_IN_MENU_HEADER => 'yes',
            CategoryLabelDictionary::AVAILABLE_SORT_BY      => 'position',
            CategoryLabelDictionary::DEFAULT_SORT_BY        => 'position'
        ];

        $updateParts = $this->getTheStoreViewsUpdateParts(
            $object,
            $context['storeViewMapping'],
            $categoryPath,
            $rootName
        );

        return array_merge([$normalized], $updateParts);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && 'api_import_category' === $format;
    }

    /**
     * Get the store views update parts which translate categories
     * Returns [['NAME_HEADER' => 'name', 'STORE_HEADER' => 'store', 'ROOT_HEADER' => 'root'], ...]
     *
     * @param CategoryInterface $category
     * @param array             $storeViewMapping
     * @param string            $rootName
     *
     * @returns array
     */
    protected function getTheStoreViewsUpdateParts(CategoryInterface $category, array $storeViewMapping, $rootName)
    {
        $updateParts = [];

        foreach ($storeViewMapping as $locale => $storeView) {
            $updateParts[] = [
                CategoryLabelDictionary::NAME_HEADER  => $this->getTranslatedName($category, $locale),
                CategoryLabelDictionary::STORE_HEADER => $storeView,
                CategoryLabelDictionary::ROOT_HEADER  => $rootName
            ];
        }

        return $updateParts;
    }

    /**
     * Returns name of the category for the given locale
     *
     * @param CategoryInterface $category
     * @param string            $locale
     *
     * @return string
     */
    protected function getTranslatedName(CategoryInterface $category, $locale)
    {
        return $category->setLocale($locale)->getLabel();
    }

    /**
     * Get tree of the given category translated with the locale
     *
     * @param CategoryInterface $category
     * @param string            $locale
     *
     * @return string[]
     */
    protected function getTranslatedCategoryTree(CategoryInterface $category, $locale)
    {
        $categoryTree = $this->categoryRepository->getPath($category);

        foreach ($categoryTree as &$categoryNode) {
            $categoryNode = $this->getTranslatedName($categoryNode, $locale);
        }

        return $categoryTree;
    }

    /**
     * Returns the category root name
     *
     * @param array $categoryTree
     * @param array $categoryMapping
     *
     * @return string
     */
    protected function getRootName(array $categoryTree, array $categoryMapping)
    {
        $root = array_shift($categoryTree);

        return $categoryMapping[$root];
    }

    /**
     * Returns the category path imploding tree with / and removing first value which is the root
     *
     * @param array $fullCatTree
     *
     * @return string
     */
    protected function getCategoryPath(array $fullCatTree)
    {
        array_shift($fullCatTree);

        return implode('/', $fullCatTree);
    }
}
