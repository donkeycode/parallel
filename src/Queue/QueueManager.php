<?php

namespace Parallel\Queue;

use Symfony\Component\Process\Process;
use Parallel\Exception\MissingCommandsException;

class QueueManager
{
    public static function buildQueueWithCommands(array $commands, string $commandPattern) : array
    {
        $processQueue = array_map(function ($command) use ($commandPattern) {
            $command = trim($command);

            if ('' === $command) {
                return null;
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
