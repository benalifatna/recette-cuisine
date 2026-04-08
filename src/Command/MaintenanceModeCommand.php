<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:maintenance-mode',
    description: 'Active or not the maintenance mode',
)]
class MaintenanceModeCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('mode', InputArgument::REQUIRED, 'ON | off')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $mode = $input->getArgument('mode');
        $maintenanceFile = $this->projectDir.'/var/maintenance.lock';

        if ('on' == $mode) {
            if (!file_exists($maintenanceFile)) {
                touch($maintenanceFile);
                $io->success('The maintenance mode is activated');
            } else {
                $io->warning('The maintenance mode is already activated');
            }
        } elseif ('off' == $mode) {
            if (file_exists($maintenanceFile)) {
                unlink($maintenanceFile);
                $io->success('The maintenance mode is desactivated');
            } else {
                $io->warning('The maintenance mode is already desactivated');
            }
        } else {
            $io->error("The maintenance mode value must be 'on' or 'off'");

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
