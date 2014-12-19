<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\FamilyRepository;

/**
 * This reader retrieves families associated to their attribute groups
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /** @var bool */
    protected $executed = false;

    /** @var \ArrayIterator */
    protected $results;

    /** @var FamilyRepository */
    protected $familyRepo;

    /**
     * @param FamilyRepository $familyRepo
     */
    public function __construct(FamilyRepository $familyRepo)
    {
        $this->familyRepo = $familyRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $nextResult = null;

        if (!$this->executed) {
            $this->results  = $this->getResults();
            $this->executed = true;
        }

        if ($this->results->valid()) {
            $nextResult = $this->results->current();
            $this->results->next();
        }

        return $nextResult;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * Returns query
     *
     * @return \Doctrine\ORM\AbstractQuery
     */
    protected function getQuery()
    {
        return $this->familyRepo
            ->createQueryBuilder('f')
            ->select('f', 'a', 'ag')
            ->join('f.attributes', 'a')
            ->join('a.group', 'ag')
            ->groupBy('f', 'ag')
            ->getQuery();
    }

    /**
     * Post process results from DB
     * Returns ['family_code' => ['family' => Family, 'groups' => [AttributeGroup, ...]]]
     *
     * @return \ArrayIterator
     */
    protected function getResults()
    {
        $queryResult = $this->getQuery()->getResult();
        $results     = [];

        foreach ($queryResult as $family) {
            $results[$family->getCode()]['family'] = $family;
            $groups = [];

            foreach ($family->getAttributes() as $attribute) {
                $group = $attribute->getGroup();

                if (!in_array($group->getCode(), $groups)) {
                    $groups[] = $group->getCode();
                    $results[$family->getCode()]['groups'][] = $group;
                }
            }
        }

        return new \ArrayIterator($results);
    }
}
