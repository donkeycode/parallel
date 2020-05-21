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

            if (!$quote) {
                $process = Process::fromShellCommandline(sprintf($commandPattern, $command));
            } else {
                $process = Process::fromShellCommandline(sprintf($commandPattern, '"$COMMAND"'));
            }
            
            $process->setTimeout(null);

            return [
                'process' => $process,
                'env' => [
                    'COMMAND' => $command
                ]
            ];
        }, $commands);
        
        $processQueue = array_filter($processQueue, function ($entry) {
            return $entry['process'] instanceof Process;
        });

        if (count($processQueue) == 0) {
            throw new MissingCommandsException();
        }

        return $processQueue;
    }
}
