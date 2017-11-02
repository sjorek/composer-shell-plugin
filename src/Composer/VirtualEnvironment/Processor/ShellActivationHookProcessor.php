<?php

/*
 * This file is part of the Composer Virtual Environment Plugin project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\Composer\VirtualEnvironment\Processor;

use Composer\Util\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;
use Sjorek\Composer\VirtualEnvironment\Config\ShellConstants;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class ShellActivationHookProcessor implements ProcessorInterface, ShellConstants
{
    use ExecutableFromTemplateTrait;

    const PROCESSOR_NAME = 'shell-hook script';

    const SHELL_HOOK_DIR = '.composer-venv/hook';

    const SHELL_HOOKS = array(
        'post-activate',
        'post-deactivate',
        'pre-activate',
        'pre-deactivate',
    );

    const SCRIPT_COMMENT = '%s shell-hook script generated by composer-virtual-environment-plugin';

    protected $hook;
    protected $name;
    protected $shell;
    protected $target;
    protected $script;
    protected $baseDir;
    protected $shellHookDir;
    protected $filesystem;

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
        $this->hook = $hook;
        $this->name = $name;
        $this->shell = $shell ?: self::SHEBANG_SH;
        $this->script = $script;
        $this->baseDir = $baseDir;
        $this->shellHookDir = $shellHookDir ?: static::SHELL_HOOK_DIR;
        $this->target = sprintf(
            '%s/%s.d/%s.%s',
            $this->shellHookDir,
            $hook,
            $name,
            basename($this->shell)
        );
        $this->filesystem = new Filesystem();
    }

    /**
     * @param  OutputInterface $output
     * @param  bool            $force
     * @return bool
     */
    public function deploy(OutputInterface $output, $force = false)
    {
        if (!in_array($this->hook, static::SHELL_HOOKS, true)) {
            $output->writeln(
                sprintf(
                    '<error>Invalid shell-hook %s given.</error>',
                    $this->hook
                )
            );

            return false;
        }

        return $this->deployTemplate($output, $force);
    }

    /**
     * @param  OutputInterface $output
     * @return bool
     */
    public function rollback(OutputInterface $output)
    {
        if (!in_array($this->hook, static::SHELL_HOOKS, true)) {
            $output->writeln(
                sprintf(
                    '<error>Invalid shell-hook %s given.</error>',
                    $this->hook
                )
            );

            return false;
        }

        return $this->rollbackTemplate($output);
    }

    /**
     * @param OutputInterface $output
     * @param bool            $force
     */
    protected function fetchTemplate(OutputInterface $output, $force)
    {
        return empty($this->script) ? false : $this->script;
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
}
