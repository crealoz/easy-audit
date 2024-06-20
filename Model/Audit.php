<?php

namespace Crealoz\EasyAudit\Model;

use Crealoz\EasyAudit\Api\Data\AuditInterface;
use Magento\Framework\Model\AbstractModel;

class Audit extends AbstractModel implements AuditInterface
{

    protected $_idFieldName = self::ID;
    private $user;
    private $date;
    private $filepath;
    private $overallResult;

    protected function _construct()
    {
        $this->_init(\Crealoz\EasyAudit\Model\ResourceModel\Audit::class);
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

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date)
    {
        $this->date = $date;
        return $this;
    }

    public function getFilepath(): ?string
    {
        return $this->filepath;
    }

    public function setFilepath(string $filepath)
    {
        $this->filepath = $filepath;
        return $this;
    }

    public function getOverallResult(): ?string
    {
        return $this->overallResult;
    }

    public function setOverallResult(string $overallResult)
    {
        $this->overallResult = $overallResult;
        return $this;
    }
}
