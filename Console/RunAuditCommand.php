<?php
namespace Crealoz\EasyAudit\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunAuditCommand extends Command
{
    public function __construct(
        protected \Crealoz\EasyAudit\Service\Audit $auditService
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('crealoz:run:audit')
            ->setDescription('Run the audit service on request');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting audit service...');

        $this->auditService->run();

        $output->writeln('Audit service has been run successfully.');
        return Command::SUCCESS;
    }
}
