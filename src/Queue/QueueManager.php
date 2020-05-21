<?php

namespace Parallel\Queue;

use Symfony\Component\Process\Process;
use Parallel\Exception\MissingCommandsException;

class QueueManager
{
    public static function buildQueueWithCommands(array $commands, string $commandPattern, bool $quote = false) : array
    {
        $processQueue = array_map(function ($command) use ($commandPattern,$quote) {
            $command = trim($command);

            if ('' === $command) {
                return null;
            }

            if ($quote) {
                $command = \sprintf('"%s"', addslashes($command));
            }

            $process = Process::fromShellCommandline(sprintf($commandPattern, $command));
            $process->setTimeout(null);

            return $process;
        }, $commands);
        
        $processQueue = array_filter($processQueue, function ($entry) {
            return $entry instanceof Process;
        });

        if (count($processQueue) == 0) {
            throw new MissingCommandsException();
        }

        return $processQueue;
    }
}
