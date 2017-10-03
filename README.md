# composer-virtual-environment-plugin
Provides a composer-plugin that adds a command to activate/deactivate the current bin directory in shell,
optionally placing a symlink to the composer- and php-binary.

    $ php composer.phar help virtual-environment
    Usage:
      virtual-environment [options]
    
    Options:
          --name=NAME                Name of the virtual environment [default: "vendor/package"]
          --php[=PHP]                Add symlink to php [default: "/usr/bin/php"]
          --composer[=COMPOSER]      Add symlink to composer [default: "composer.phar"]
      -f, --force[=FORCE]            Force overwriting existing environment scripts [default: false]
      -h, --help                     Display this help message
      -q, --quiet                    Do not output any message
      -V, --version                  Display this application version
          --ansi                     Force ANSI output
          --no-ansi                  Disable ANSI output
      -n, --no-interaction           Do not ask any interactive question
          --profile                  Display timing and memory usage information
          --no-plugins               Whether to disable plugins.
      -d, --working-dir=WORKING-DIR  If specified, use the given directory as working directory.
      -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose
                                     output and 3 for debug
    
    Help:
      The virtual-environment command creates files
      to activate/deactivate the current bin directory in shell,
      optionally placing a symlink to the current php-binary.
      
      php composer.phar virtual-environment
      
      After this you can source the activation-script
      corresponding to your shell:
      
      bash/zsh:
      
          $ source vendor/bin/activate
      
      csh:
      
          $ source vendor/bin/activate.csh
      
      fish:
      
          $ . vendor/bin/activate.fish
      
      bash (alternative):
      
          $ source vendor/bin/activate.bash
      
      zsh (alternative):
      
          $ source vendor/bin/activate.zsh

