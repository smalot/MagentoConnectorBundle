<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader;

use Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\Reader;
use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Categories filtered by channel reader
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryReader extends Reader
{
    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var ChannelManager */
    protected $channelManager;

    /**
     * Channel code
     *
     * @var string
     */
    protected $channel;

    /**
     * @param CategoryRepository $categoryRepository
     * @param ChannelManager     $channelManager
     */
    public function __construct(CategoryRepository $categoryRepository, ChannelManager $channelManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->channelManager     = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        if (!$this->query) {
            $qb = $this->categoryRepository->createQueryBuilder('c');

            $qb
                ->where($qb->expr()->eq('c.root', $this->getChannelTree()->getId()))
                ->andWhere($qb->expr()->neq('c.level', 0))
                ->orderBy('c.root')
                ->addOrderBy('c.left');

            $this->query = $qb->getQuery();
        }

        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'channel' => [
                'type'    => 'choice',
                'options' => [
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                ]
            ]
        ];
    }

    /**
     * Set channel code
     *
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel code
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Returns root category associated to the chosen channel
     *
     * @return \Pim\Bundle\CatalogBundle\Model\CategoryInterface
     */
    protected function getChannelTree()
    {
        $channel = $this->getChosenChannelEntity();

        return $channel->getCategory();
    }

    /**
     * Get the chosen channel entity
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    protected function getChosenChannelEntity()
    {
        return $this->channelManager->getChannelByCode($this->channel);
    }
}
