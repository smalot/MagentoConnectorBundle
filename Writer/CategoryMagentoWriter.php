<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Guesser\MagentoWebserviceGuesser;
use Pim\Bundle\MagentoConnectorBundle\Manager\CategoryMappingManager;

/**
 * Magento category writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class CategoryMagentoWriter extends AbstractMagentoWriter
{
    /**
     * @var CategoryMappingManager
     */
    protected $categoryMappingManager;

    /**
     * Constructor
     *
     * @param ChannelManager           $channelManager
     * @param MagentoWebserviceGuesser $magentoWebserviceGuesser
     * @param CategoryMappingManager   $categoryMappingManager
     */
    public function __construct(
        ChannelManager $channelManager,
        MagentoWebserviceGuesser $magentoWebserviceGuesser,
        CategoryMappingManager $categoryMappingManager
    ) {
        parent::__construct($channelManager, $magentoWebserviceGuesser);

        $this->categoryMappingManager = $categoryMappingManager;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $categories)
    {
        $this->beforeProcess();

        //creation for each product in the admin storeView (with default locale)
        foreach ($categories as $batch) {
            foreach ($batch['create'] as $newCategory) {
                var_dump($newCategory['magentoCategory']);
                $pimCategory       = $newCategory['pimCategory'];
                $magentoCategoryId = $this->magentoWebservice->sendNewCategory($newCategory['magentoCategory']);
                $magentoUrl        = $this->soapUrl;

                $this->categoryMappingManager->registerCategoryMapping(
                    $pimCategory,
                    $magentoCategoryId,
                    $magentoUrl
                );
            }

            foreach ($batch['update'] as $updateCategory) {
                var_dump('update');
                var_dump($updateCategory);
                $this->magentoWebservice->sendUpdateCategory($updateCategory);
            }

            foreach ($batch['move'] as $moveCategory) {
                var_dump('move');
                var_dump($moveCategory);
                $this->magentoWebservice->sendMoveCategory($moveCategory);
            }
        }
    }
}
