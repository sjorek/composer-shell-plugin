<?php

/*
 * This file is part of the Composer Virtual Environment Plugin project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\Composer\VirtualEnvironment\Command;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Util\Platform;
use Sjorek\Composer\VirtualEnvironment\Command\Config\CommandConfigurationInterface;
use Sjorek\Composer\VirtualEnvironment\Command\Config\ShellActivatorConfiguration;
use Sjorek\Composer\VirtualEnvironment\Processor;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class ShellActivatorCommand extends AbstractProcessorCommand
{
    protected function configure()
    {
//         $shells = exlode(',', ShellActivatorConfiguration::SHELLS_POSIX);
//         if (!(($shell = getenv('SHELL')) && ($shell = basename($shell)) && in_array($shell, $shells))) {
//             $shell = null;
//         }
        $this
            ->setName('virtual-environment:shell')
            ->setAliases(array('venv:shell'))
            ->setDescription('Add or remove virtual environment shell activation scripts.')
            ->setDefinition(
                $this->addDefaultDefinition(
                    array(
                        new InputArgument(
                            'shell',
                            InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                            'List of shell activators to add or remove.'
                        ),
                        new InputOption(
                            'name',
                            null,
                            InputOption::VALUE_REQUIRED,
                            'Name of the virtual environment.',
                            '{$name}'
                        ),
                        new InputOption(
                            'colors',
                            null,
                            InputOption::VALUE_NONE,
                            'Enable the color prompt per default. Works currently only for "bash".'
                        ),
                        new InputOption(
                            'no-colors',
                            null,
                            InputOption::VALUE_NONE,
                            'Disable the color prompt per default.'
                        ),
//                         new InputOption(
//                             'deploy-dir',
//                             null,
//                             InputOption::VALUE_REQUIRED,
//                             'Deploy the given list of shells to given directory.',
//                             '{$bin-dir}'
//                         ),
                    )
                )
            )
            ->setHelp(
                <<<EOT
The <info>virtual-environment:shell</info> command creates files to
activate and deactivate the current bin directory in shell.

Usage:

    <info>php composer.phar venv:shell</info>

After this you can source the activation-script
corresponding to your shell.

Posix compatible shells:

    If one posix shell-activator or
    bash and zsh have been deployed:
    <info>source vendor/bin/activate</info>

    csh:
        <info>source vendor/bin/activate.csh</info>

    fish:
        <info>. vendor/bin/activate.fish</info>

    bash (alternative):
        <info>source vendor/bin/activate.bash</info>

    zsh (alternative):
        <info>source vendor/bin/activate.zsh</info>

NT (Windows) compatible shells:

    cmd[.exe] (batch):
        <info>vendor\\bin\\activate.bat</info>

    powershell[.exe]:
        <info>vendor\\bin\\Activate.ps1</info>

EOT
            );
    }

    /**
     * {@inheritDoc}
     * @see AbstractProcessorCommand::getCommandConfiguration()
     */
    protected function getCommandConfiguration(
        InputInterface $input,
        OutputInterface $output,
        Composer $composer,
        IOInterface $io
    ) {
        return new ShellActivatorConfiguration($input, $output, $composer, $io);
    }

    const BASH_TEMPLATE_COMMANDS = array(
        '@TPUT_COLORS@' => 'tput colors',
        '@TPUT_BOLD@' => 'tput bold',
        '@TPUT_SMUL@' => 'tput smul',
        '@TPUT_SMSO@' => 'tput smso',
        '@TPUT_SGR0@' => 'tput sgr0',
        '@TPUT_SETAF_0@' => 'tput setaf 0',
        '@TPUT_SETAF_1@' => 'tput setaf 1',
        '@TPUT_SETAF_2@' => 'tput setaf 2',
        '@TPUT_SETAF_3@' => 'tput setaf 3',
        '@TPUT_SETAF_4@' => 'tput setaf 4',
        '@TPUT_SETAF_5@' => 'tput setaf 5',
        '@TPUT_SETAF_6@' => 'tput setaf 6',
        '@TPUT_SETAF_7@' => 'tput setaf 7',
    );

    /**
     * {@inheritDoc}
     * @throws \RuntimeException
     * @see AbstractProcessorCommand::deploy()
     */
    protected function deploy(CommandConfigurationInterface $config, OutputInterface $output)
    {
        $activators = $config->get('shell-expanded');
        if (empty($activators)) {
            $output->writeln(
                '<error>Skipping creation of shell activators, none available.</error>'
            );
        } else {
            $baseDir = $config->get('base-dir');
            $binDir = $config->get('bin-dir');
            $resourceDir = $config->get('resource-dir');
            $dataTemplate = array(
                '@NAME@' => $config->get('name-expanded'),
                '@BASE_DIR@' => $baseDir,
                '@BIN_DIR@' => $baseDir . '/' . $binDir,
                '@BIN_PATH@' => $binDir,
                '@SHELL_HOOK_DIR@' => $baseDir . '/' . $config->get('shell-hook-dir-expanded'),
                '@COLORS@' => $config->get('colors') ? '1' : '0',
            );
            $windowsShells = explode(',', ShellActivatorConfiguration::SHELLS_NT);
            foreach ($activators as $name => $activator) {
                $data = array_merge($dataTemplate, array('@SHEBANG@' => $activator['shell']));
                if ($name === 'bash') {
                    $this->fillBashTemplateData($data, $activator['shell']);
                } elseif (in_array($name, $windowsShells, true)) {
                    $this->convertWindowsDirectorySeparator(
                        $data,
                        array(
                            '@BASE_DIR@',
                            '@BIN_DIR@',
                            '@BIN_PATH@',
                            '@SHELL_HOOK_DIR@',
                        )
                    );
                }
                foreach ($activator['filenames'] as $file) {
                    $source = $resourceDir . '/' . $file;
                    $target = $binDir . '/' . $file;
                    $processor = new Processor\ShellActivationScriptProcessor($source, $target, $baseDir, $data);
                    $processor->deploy($output, $config->get('force'));
                }
            }
            if ($config->has('shell-link-expanded')) {
                $symlinks = $config->get('shell-link-expanded');
                if (empty($symlinks)) {
                    $output->writeln(
                        '<comment>Skipping creation of symbolic link to shell activation script, as none is needed.</comment>',
                        OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_VERBOSE
                    );
                } elseif (Platform::isWindows()) {
                    $output->writeln(
                        '<error>Symbolic link to shell activation script is not (yet) supported on windows.</error>',
                        OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_VERBOSE
                    );
                } else {
                    foreach ($symlinks as $source => $target) {
                        $processor = new Processor\SymbolicLinkProcessor($source, $target, $baseDir);
                        $processor->deploy($output, $config->get('force'));
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     * @see AbstractProcessorCommand::rollback()
     */
    protected function rollback(CommandConfigurationInterface $config, OutputInterface $output)
    {
        $activators = $config->get('shell-expanded');
        if (empty($activators)) {
            $output->writeln(
                '<error>Skipping removal of shell activation scripts, as none is available.</error>'
            );
        } else {
            $baseDir = $config->get('base-dir');
            $binDir = $config->get('bin-dir');
            $resourceDir = $config->get('resource-dir');
            foreach ($activators as $activator) {
                foreach ($activator['filenames'] as $file) {
                    $source = $resourceDir . '/' . $file;
                    $target = $binDir . '/' . $file;
                    $processor = new Processor\ShellActivationScriptProcessor($source, $target, $baseDir, array());
                    $processor->rollback($output);
                }
            }
            if ($config->has('shell-link-expanded')) {
                $symlinks = $config->get('shell-link-expanded');
                if (empty($symlinks)) {
                    $output->writeln(
                        '<comment>Skipping removal of symbolic link to shell activation script, as none is needed.</comment>',
                        OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_VERBOSE
                    );
                } elseif (Platform::isWindows()) {
                    $output->writeln(
                        '<error>Symbolic link to shell activation script is not (yet) supported on windows.</error>',
                        OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_VERBOSE
                    );
                } else {
                    foreach ($symlinks as $source => $target) {
                        $processor = new Processor\SymbolicLinkProcessor($source, $target, $baseDir);
                        $processor->rollback($output);
                    }
                }
            }
        }
    }

    /**
     * @param  array             $data
     * @param  string            $bash
     * @throws \RuntimeException
     */
    protected function fillBashTemplateData(array & $data, $bash)
    {
        // TODO check that $bash is really a bash? check version or issue a command only bash supports?
        $process = new Process(null);
        foreach (self::BASH_TEMPLATE_COMMANDS as $key => $command) {
            $process->setCommandLine(
                sprintf(
                    '( echo %s | %s -ls ) 2>/dev/null',
                    escapeshellarg($command),
                    $bash // already escaped
                )
            );
            $process->run();
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }
            $data[$key] = trim($process->getOutput());
        }
    }

    /**
     * @param array $data
     * @param array $keys
     */
    protected function convertWindowsDirectorySeparator(array & $data, array $keys)
    {
        foreach ($keys as $key) {
            $data[$key] = implode('\\', explode('/', $data[$key]));
        }
    }
}
