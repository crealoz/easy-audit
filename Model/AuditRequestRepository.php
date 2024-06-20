<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\AuditRequestRepositoryInterface;
use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class AuditRequestRepository implements AuditRequestRepositoryInterface
{
    public function __construct(
        protected \Crealoz\EasyAudit\Model\ResourceModel\AuditRequest $auditRequestResource,
        protected \Crealoz\EasyAudit\Model\AuditRequestFactory $auditRequestFactory
    )
    {
    }

    public function save(AuditRequestInterface $auditRequest): void
    {
        $this->auditRequestResource->save($auditRequest);
    }

    public function getById(int $id): AuditRequestInterface
    {
        $auditRequest = $this->auditRequestFactory->create();
        $this->auditRequestResource->load($auditRequest, $id);
        return $auditRequest;
    }

    public function delete(AuditRequestInterface $auditRequest): void
    {
        $this->auditRequestResource->delete($auditRequest);
    }

    public function deleteById(int $id): void
    {
        $auditRequest = $this->getById($id);
        $this->delete($auditRequest);
    }

    public function getList(SearchCriteriaInterface $searchCriteria): \Crealoz\EasyAudit\Api\Data\AuditRequestSearchResultsInterface
    {
        $auditRequestCollection = $this->auditRequestFactory->create()->getCollection();
        $auditRequestCollection->addFilters($searchCriteria);
        return $auditRequestCollection->getItems();
    }
}
