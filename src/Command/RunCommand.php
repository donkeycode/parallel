<?php

namespace Parallel\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;
use Parallel\Queue\QueueManager;
use Parallel\Exception\MissingCommandsException;

class RunCommand extends Command
{
    protected static $defaultName = 'run';

    protected function configure()
    {
        $this
            ->setDescription('Run multi-threaded process')
            ->addArgument('commands', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'The commands list')
            ->addOption('threads', 't', InputOption::VALUE_REQUIRED, 'Number of threads to run', 10)
            ;
    }

    private function defineCommands(InputInterface $input, OutputInterface $output)
    {
        if ($input->getArgument('commands')) {
            // Ok command is defined
            return;
        }

        if (posix_isatty(STDIN)) {
            throw new MissingCommandsException();
        }

        $commands = explode("\n", trim(stream_get_contents(fopen("php://stdin", "r"))));

        if (count($commands) > 0) {
            $input->setArgument('commands', $commands);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $this->defineCommands($input, $output);

        $processQueue = QueueManager::buildQueueWithCommands($input->getArgument('commands'));
        $processLimit = $input->getOption('threads');

        /* @var Process[] $processCurrent */
        $processCurrent = [];

        while (count($processQueue) || count($processCurrent)) {
            // remove finished processes
            foreach ($processCurrent as $index => $process) {
                if (!$process->isRunning()) {
                    $commandLine = trim($process->getCommandLine());
                    $output->writeln(trim($process->getOutput()));

                    if (trim($process->getErrorOutput())) {
                        $output->getErrorOutput()->writeln('<error>ERROR</error> '.trim($process->getErrorOutput()));
                    }

                    $output->writeln('<info>['.date('H:i:s').'] Finish Process: '.$commandLine.'</info>');
                    $output->writeln('');

                    unset($processCurrent[$index]);
                }
            }

            // start new processes
            if ($processLimit > count($processCurrent) && count($processQueue)) {
                /** @var Process $process */
                $process = array_shift($processQueue);
                $output->writeln('<info>['.date('H:i:s').'] Start Process: '.$process->getCommandLine().'</info>');
                $process->start();
                $processCurrent[] = $process;
            }

            // Wait befor checking evolution of script
            usleep(1000);
        }

        return 0;
    }

}