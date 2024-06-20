<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\AuditRepositoryInterface;
use Crealoz\EasyAudit\Api\Data\AuditInterface;
use Crealoz\EasyAudit\Api\Data\AuditSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class AuditRepository implements AuditRepositoryInterface
{
    public function __construct(
        protected \Crealoz\EasyAudit\Model\ResourceModel\Audit $auditResource,
        protected \Crealoz\EasyAudit\Model\AuditFactory $auditFactory
    )
    {
    }

    public function getById(int $id): AuditInterface
    {
        $audit = $this->auditFactory->create();
        $this->auditResource->load($audit, $id);
        return $audit;
    }

    public function save(AuditInterface $audit): AuditInterface
    {
        $this->auditResource->save($audit);
        return $audit;
    }

    public function delete(AuditInterface $audit): void
    {
        $this->auditResource->delete($audit);
    }

    public function getList(SearchCriteriaInterface $searchCriteria): AuditSearchResultsInterface
    {
        $collection = $this->auditFactory->create()->getCollection();
        $collection->addFilters($searchCriteria);
        return $collection->getSearchResults();
    }

    public function deleteById(int $id): void
    {
        $audit = $this->getById($id);
        $this->delete($audit);
    }
}
