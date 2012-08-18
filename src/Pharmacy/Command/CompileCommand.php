<?php

/*
 * This file is part of Pharmacy.
 *
 * (c) Rob Loach <robloach@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Pharmacy\Command;

use Pharmacy\Compiler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Rob Loach <robloach@gmail.com>
 */
class CompileCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('compile')
            ->setDescription('Parses the given pharmacy.json file into a phar archive.')
            ->addArgument('file', InputArgument::OPTIONAL, 'The desired pharmancy.json file to process.', 'pharmacy.json')
            ->setHelp(<<<EOT
The <info>compile</info> command reads the pharmacy.json file from the
current directory, processes it, and compiles the application into a
.phar archive.

<info>php pharmacy.phar compile</info>

EOT
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        $compiler = Compiler::fromFile($file);

        return $compiler->compile() ? 0 : 1;
    }
}
