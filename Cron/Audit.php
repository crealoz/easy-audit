<?php
namespace Crealoz\EasyAudit\Cron;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Audit
{
    public function __construct(
        protected \Crealoz\EasyAudit\Service\Audit $auditService
    )
    {

    }

    public function execute()
    {
        // Check for any pending audit requests and execute them
        // You will need to create a model and resource model for the audit request table
    }
}
