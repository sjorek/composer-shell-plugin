<?php

/*
 * This file is part of the Composer Virtual Environment Plugin project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\Composer\VirtualEnvironment\Processor\ShellHook;

use Sjorek\Composer\VirtualEnvironment\Config\ShellConstants;
use Sjorek\Composer\VirtualEnvironment\Processor\ExecutableFromTemplateTrait;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class ScriptProcessor extends AbstractProcessor implements ShellConstants
{
    use ExecutableFromTemplateTrait;

    const PROCESSOR_NAME = 'shell-hook script';
    const SCRIPT_COMMENT = '%s shell-hook script generated by composer-virtual-environment-plugin';

    protected $shebang;

    /**
     * @param string $hook
     * @param string $name
     * @param string $shell
     * @param string $script
     * @param string $baseDir
     * @param string $shellHookDir
     */
    public function __construct($hook, $name, $shell, $script, $baseDir, $shellHookDir = null)
    {
        parent::__construct($hook, $name, $shell, $script, $baseDir, $shellHookDir);
    }

    /**
     * {@inheritDoc}
     * @see \Sjorek\Composer\VirtualEnvironment\Processor\ShellHook\AbstractProcessor::deployHook()
     */
    protected function deployHook(OutputInterface $output, $force)
    {
        return $this->deployTemplate($output, $force);
    }

    /**
     * @param OutputInterface $output
     * @param bool            $force
     */
    protected function fetchTemplate(OutputInterface $output, $force)
    {
        return empty($this->source) ? false : $this->source;
    }

    /**
     * @param  string          $content
     * @param  OutputInterface $output
     * @param  string          $force
     * @return string|bool
     */
    protected function renderTemplate($content, OutputInterface $output, $force = false)
    {
        $shebang = $this->shell === 'sh' ? self::SHEBANG_SH : $this->shell;
        $shebang = explode(' ', $shebang, 2);
        if (!$this->filesystem->isAbsolutePath($shebang[0])) {
            $shebang = explode(' ', sprintf(self::SHEBANG_ENV, implode(' ', $shebang)), 2);
        }
        $shebang[0] = $this->filesystem->normalizePath($shebang[0]);
        if (!(file_exists($shebang[0]) || is_link($shebang[0]))) {
            $output->writeln(
                sprintf(
                    '<error>The shebang executable "%s" does not exist for shell-hook script: %s</error>',
                    $shebang[0],
                    $content
                ),
                OutputInterface::OUTPUT_NORMAL | OutputInterface::VERBOSITY_QUIET
            );
        }
        $shebang = trim(implode(' ', $shebang));
        $content = implode(
            PHP_EOL,
            array(
                '# ' . sprintf(static::SCRIPT_COMMENT, $this->hook),
                trim($content),
                '',
            )
        );

        return sprintf('#!%s%s%s', $shebang, PHP_EOL, $content);
    }

    /**
     * {@inheritDoc}
     * @see \Sjorek\Composer\VirtualEnvironment\Processor\ShellHook\AbstractProcessor::rollbackHook()
     */
    protected function rollbackHook(OutputInterface $output)
    {
        return $this->rollbackTemplate($output);
    }
}
