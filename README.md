# “virtual-environment” command-plugin for composer

A composer-plugin adding a command to activate/deactivate the current
bin-directory in shell, optionally creating symlinks to the composer-
and php-binary in the bin-directory.

## Usage

    $ php composer.phar help virtual-environment
    Usage:
      virtual-environment [options]
    
    Options:
          --name=NAME                Name of the virtual environment.
                                     Takes precedence over recipe and
                                     global composer configuration.
                                     [default: "package/name"]
          --shell=SHELL              Set the list of shell activators to
                                     deploy.
                                     Takes precedence over recipe and
                                     global composer configuration.
                                     [default: ["bash","csh","fish",
                                     "zsh"]] (multiple values allowed)
          --php=PHP                  Add symlink to php.
                                     Takes precedence over recipe and
                                     global composer configuration.
          --composer=COMPOSER        Add symlink to composer.
                                     Takes precedence over recipe and
                                     global composer configuration.
          --use-recipe               Use and update the virtual
                                     environment configuration recipe in
                                     "./composer.venv".
                                     Takes precedence over global
                                     composer configuration.
          --use-composer             Use and update the global composer
                                     configuration
                                     "~/.composer/config.json".
      -f, --force                    Force overwriting existing
                                     environment scripts
      -h, --help                     Display this help message
      -q, --quiet                    Do not output any message
      -V, --version                  Display this application version
          --ansi                     Force ANSI output
          --no-ansi                  Disable ANSI output
      -n, --no-interaction           Do not ask any interactive question
          --profile                  Display timing and memory usage
                                     information
          --no-plugins               Whether to disable plugins.
      -d, --working-dir=WORKING-DIR  If specified, use the given
                                     directory as working directory.
      -v|vv|vvv, --verbose           Increase the verbosity of messages:
                                     1 for normal output, 2 for more
                                     verbose output and 3 for debug
    
    Help:
      The virtual-environment command creates files to activate
      and deactivate the current bin directory in shell,
      optionally placing a symlinks to php- and composer-binaries
      in the bin directory.
      
      Usage:
      
      php composer.phar virtual-environment
      
      After this you can source the activation-script
      corresponding to your shell:
      
      bash/zsh:
      
          source vendor/bin/activate
      
      csh:
      
          source vendor/bin/activate.csh
      
      fish:
      
          . vendor/bin/activate.fish
      
      bash (alternative):
      
          source vendor/bin/activate.bash
      
      zsh (alternative):
      
          source vendor/bin/activate.zsh
      
