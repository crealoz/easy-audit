<?php

namespace Crealoz\EasyAudit\Api\Data;

interface AuditRequestInterface
{
    const ID = 'request_id';
    const USER = 'user';
    const EXECUTION_TIME = 'execution_time';

    /**
     * @return string|null
     */
    public function getUser(): ?string;

    /**
     * @param string $user
     * @return $this
     */
    public function setUser(string $user);

    /**
     * @return string|null
     */
    public function getExecutionTime(): ?string;

    /**
     * @param string $executionTime
     * @return $this
     */
    public function setExecutionTime(string $executionTime);
}