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
            ->addArgument('commands', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'The commands list')
            ->addOption('threads', 't', InputOption::VALUE_REQUIRED, 'Number of threads to run', 10)
            ->addOption('pattern', 'p', InputOption::VALUE_REQUIRED, 'Define a command pattern (eg bin/console basic:command %s --option', '%s')
            ->addOption('quote', null, InputOption::VALUE_NONE, 'Add slashes and quotes, use with command pattern to keep one argument with addslashes')
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

        $processQueue = QueueManager::buildQueueWithCommands($input->getArgument('commands'), $input->getOption('pattern'), $input->getOption('quote'));
        $processLimit = $input->getOption('threads');

        /* @var Process[] $processCurrent */
        $processCurrent = [];

        while (count($processQueue) || count($processCurrent)) {
            // remove finished processes
            foreach ($processCurrent as $index => $process) {
                if (!$process['process']->isRunning()) {
                    $commandLine = trim($process['process']->getCommandLine());
                    $output->writeln(trim($process['process']->getOutput()));

                    if (trim($process['process']->getErrorOutput())) {
                        if (\method_exists($output, 'getErrorOutput')) {
                            $output->getErrorOutput()->writeln('<error>ERROR</error> '.trim($process['process']->getErrorOutput()));
                        }
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
                $output->writeln('<info>['.date('H:i:s').'] Start Process: '.$process['process']->getCommandLine().'</info>');
                $process['process']->start(null, $process['env']);
                $processCurrent[] = $process;
            }

            // Wait befor checking evolution of script
            usleep(1000);
        }

        return 0;
    }

}