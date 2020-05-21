<?php

namespace Parallel\Test;

use PHPUnit\Framework\TestCase;
use Parallel\ApplicationFactory;
use Symfony\Component\Console\Tester\CommandTester;
use Parallel\Exception\MissingCommandsException;

class RunCommandTest extends TestCase
{
    private $commandTester;

    const TEST_COMMANDS = [
        "echo 'Hello guy'",
        "sleep 1 && echo 'after sleep'",
        "echo 'bob is great'",
    ];

    public function setUp() : void
    {
        $application = ApplicationFactory::create();
        $command = $application->find('run');
        $this->commandTester = new CommandTester($command);
    }

    public function testRunWithoutCommands()
    {
        $this->expectException(MissingCommandsException::class);
        $this->commandTester->execute([]);
    }

    public function testRunOneThread()
    {
        $this->commandTester->execute([
            'commands' => self::TEST_COMMANDS,
            '--threads'  => 1
        ]);
        $output = $this->commandTester->getDisplay();
        \preg_match_all('#Start Process: (.+)#', $output, $startMatches);
        \preg_match_all('#Finish Process: (.+)#', $output, $endMatches);

        $this->assertEquals(self::TEST_COMMANDS, $startMatches[1], 'Process start running ordered');
        $this->assertEquals(self::TEST_COMMANDS, $endMatches[1], 'When running one thread order is conserved');
    }

    public function testRunWithPattern()
    {
        $this->commandTester->execute([
            'commands' => self::TEST_COMMANDS,
            '--threads'  => 1,
            '--pattern'  => 'echo "Before" && %s && echo "After"'
        ]);

        $output = $this->commandTester->getDisplay();
        \preg_match_all('#Start Process: (.+)#', $output, $startMatches);

        $this->assertEquals('echo "Before" && '.self::TEST_COMMANDS[0].' && echo "After"', $startMatches[1][0], 'Pattern is respected');
    }

    public function testRunMultiThread()
    {
        $this->commandTester->execute([
            'commands' => self::TEST_COMMANDS,
        ]);
        $output = $this->commandTester->getDisplay();
        \preg_match_all('#Start Process: (.+)#', $output, $startMatches);
        \preg_match_all('#Finish Process: (.+)#', $output, $endMatches);

        $this->assertEquals(self::TEST_COMMANDS, $startMatches[1], 'Process start running ordered');
        $this->assertEquals([
            "echo 'Hello guy'",
            "echo 'bob is great'",
            "sleep 1 && echo 'after sleep'",
        ], $endMatches[1], 'When running one thread order is conserved');
    }
}