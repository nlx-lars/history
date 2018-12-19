<?php
namespace AE\History\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Neos\EventLog\Domain\Model\NodeEvent;
use Neos\Neos\EventLog\Domain\Repository\EventRepository;

/**
 * The repository for events
 *
 * @Flow\Scope("singleton")
 */
class NodeEventRepository extends EventRepository
{
    const ENTITY_CLASSNAME = NodeEvent::class;

    /**
     * Find all events which are "top-level" and in a given workspace (or are not NodeEvents)
     *
     * @param integer $offset
     * @param integer $limit
     * @param string $workspaceName
     * @param string $siteIdentifier
     * @param string $nodeIdentifier
     * @param string $accountIdentifier
     * @param \DateTime $from
     * @param \DateTime $to
     * @return QueryResultInterface
     */
    public function findRelevantEventsByWorkspace($offset, $limit, $workspaceName, $siteIdentifier = null, $nodeIdentifier = null, $accountIdentifier = null, $from = null, $to = null)
    {
        $query = $this->prepareRelevantEventsQuery();
        $query->getQueryBuilder()
            ->andWhere('e.workspaceName = :workspaceName AND e.eventType = :eventType')
            ->setParameter('workspaceName', $workspaceName)
            ->setParameter('eventType', 'Node.Published');
        if ($siteIdentifier) {
            $siteCondition = '%' . trim(json_encode(['site' => $siteIdentifier], JSON_PRETTY_PRINT), "{}\n\t ") . '%';
            $query->getQueryBuilder()
                ->andWhere('NEOSCR_TOSTRING(e.data) LIKE :site')
                ->setParameter('site', $siteCondition);
        }
        if ($nodeIdentifier) {
            $query->getQueryBuilder()
                ->andWhere('e.nodeIdentifier = :nodeIdentifier')
                ->setParameter('nodeIdentifier', $nodeIdentifier);
        }
        if ($accountIdentifier) {
            $query->getQueryBuilder()
                ->andWhere('e.accountIdentifier = :accountIdentifier')
                ->setParameter('accountIdentifier', $accountIdentifier);
        }
        if ($from) {
            $query->getQueryBuilder()
                ->andWhere('e.timestamp >= :from')
                ->setParameter('from', $from);
        }
        if ($to) {
            $query->getQueryBuilder()
                ->andWhere('e.timestamp <= :to')
                ->setParameter('to', $to);
        }
        $query->getQueryBuilder()->setFirstResult($offset);
        $query->getQueryBuilder()->setMaxResults($limit);

        return $query->execute();
    }

}
