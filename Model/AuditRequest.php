<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\Data\AuditRequestInterface;
use Magento\Framework\Model\AbstractModel;

class AuditRequest extends AbstractModel implements AuditRequestInterface
{
    protected $_idFieldName = self::ID;
    private $user;
    private $executionTime;

    protected function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\ResourceModel\AuditRequest::class);
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getExecutionTime(): ?string
    {
        return $this->executionTime;
    }

    public function setExecutionTime(string $executionTime)
    {
        $this->executionTime = $executionTime;
        return $this;
    }
}
