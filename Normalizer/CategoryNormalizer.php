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
    public function normalize($category, $format = null, array $context = [])
    {
        $defaultLocale = $context['defaultLocale'];
        $rootName      = $this->getRootName($category, $context['userCategoryMapping'], $defaultLocale);
        $categoryPath  = $this->getFormattedPath($category, $defaultLocale);
        $categoryName  = $this->getTranslatedName($category, $defaultLocale);

        $normalized = [
            CategoryLabelDictionary::ROOT_HEADER              => $rootName,
            CategoryLabelDictionary::NAME_HEADER              => $categoryName,
            CategoryLabelDictionary::CATEGORY_HEADER          => $categoryPath,
            CategoryLabelDictionary::ACTIVE_HEADER            => 'yes',
            CategoryLabelDictionary::POSITION_HEADER          => $category->getLeft(),
            CategoryLabelDictionary::INCLUDE_IN_MENU_HEADER   => 'yes',
            CategoryLabelDictionary::AVAILABLE_SORT_BY_HEADER => 'position',
            CategoryLabelDictionary::DEFAULT_SORT_BY_HEADER   => 'position'
        ];

        $storeViewParts = $this->getStoreViewParts(
            $category,
            $context['storeViewMapping'],
            $rootName
        );

        return array_merge([$normalized], $storeViewParts);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof CategoryInterface && 'api_import_category' === $format;
    }

    /**
     * Get the store views parts which translate categories
     * Returns [['NAME_HEADER' => 'name', 'STORE_HEADER' => 'store', 'ROOT_HEADER' => 'root'], ...]
     *
     * @param CategoryInterface $category
     * @param array             $storeViewMapping
     * @param string            $rootName
     *
     * @return array
     */
    protected function getStoreViewParts(CategoryInterface $category, array $storeViewMapping, $rootName)
    {
        $storeViewParts = [];

        foreach ($storeViewMapping as $locale => $storeView) {
            $storeViewParts[] = [
                CategoryLabelDictionary::NAME_HEADER  => $this->getTranslatedName($category, $locale),
                CategoryLabelDictionary::STORE_HEADER => $storeView,
                CategoryLabelDictionary::ROOT_HEADER  => $rootName
            ];
        }

        return $storeViewParts;
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
     * Returns the category root name
     *
     * @param CategoryInterface $category
     * @param array             $rootCategoryMapping
     * @param string            $defaultLocale
     *
     * @return string
     */
    protected function getRootName(CategoryInterface $category, array $rootCategoryMapping, $defaultLocale)
    {
        $rootId   = $category->getRoot();
        $root     = $this->categoryRepository->getCategoriesByIds([$rootId])->first();
        $rootName = $this->getTranslatedName($root, $defaultLocale);

        return $rootCategoryMapping[$rootName];
    }

    /**
     * Returns the category path imploding with / and without root
     *
     * @param CategoryInterface $category
     * @param string            $locale
     *
     * @return string
     */
    protected function getFormattedPath(CategoryInterface $category, $locale)
    {
        $translatedPath = [];
        $categoryPath = $this->categoryRepository->getPath($category);
        unset($categoryPath[0]);

        foreach ($categoryPath as $categoryNode) {
            $translatedPath[] = $this->getTranslatedName($categoryNode, $locale);
        }

        return implode(CategoryLabelDictionary::SEPARATOR, $translatedPath);
    }
}
