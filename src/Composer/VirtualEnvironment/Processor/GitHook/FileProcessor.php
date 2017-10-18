<?php

/*
 * This file is part of Composer Virtual Environment Plugin.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\Composer\VirtualEnvironment\Processor\GitHook;

use Sjorek\Composer\VirtualEnvironment\Processor\ExecutableFromTemplateTrait;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class FileProcessor extends AbstractProcessor
{
    use ExecutableFromTemplateTrait;

    const PROCESSOR_NAME = 'git-hook file';

    /**
     * @param string $name
     * @param string $file
     * @param string $baseDir
     * @param string $gitHookDir
     */
    public function __construct($name, $file, $baseDir, $gitHookDir = null)
    {
        parent::__construct($name, $file, $baseDir, $gitHookDir);
    }

    /**
     * {@inheritDoc}
     * @see \Sjorek\Composer\VirtualEnvironment\Processor\GitHook\AbstractProcessor::deployHook()
     */
    protected function deployHook(OutputInterface $output, $force)
    {
        return $this->deployTemplate($output, $force);
    }

    /**
     * {@inheritDoc}
     * @see \Sjorek\Composer\VirtualEnvironment\Processor\GitHook\AbstractProcessor::rollbackHook()
     */
    protected function rollbackHook(OutputInterface $output)
    {
        return $this->rollbackTemplate($output);
    }
}
