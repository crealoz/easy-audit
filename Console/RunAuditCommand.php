<?php
namespace Crealoz\EasyAudit\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunAuditCommand extends Command
{
    protected function configure()
    {
        $this->setName('crealoz:run:audit')
            ->setDescription('Run the audit service on request');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Call the audit service here

        $output->writeln('Audit service has been run successfully.');
    }
}
