<?php

namespace Parallel;

use Symfony\Component\Console\Application;

class ApplicationFactory
{
    public static function create() : Application
    {
        $application = new Application();
        $application->add(new Command\RunCommand());
        $application->setDefaultCommand('run', true);

        return $application;
    }
}


