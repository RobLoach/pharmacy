<?php

/*
 * This file is part of Pharmacy.
 *
 * (c) Rob Loach <robloach@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pharmacy\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Pharmacy\Command;

/**
 * The console application that handles the commands
 *
 * @author Rob Loach <robloach@gmail.com>
 */
class Application extends BaseApplication
{
    /**
     * Initializes all the composer commands
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Command\CompileCommand();

        return $commands;
    }
}
