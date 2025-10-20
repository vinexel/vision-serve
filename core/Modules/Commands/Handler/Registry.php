<?php

namespace Vinexel\Modules\Commands\Handler;

use Vinexel\Modules\Commands\Helpers\ServeCommand;
use Vinexel\Modules\Commands\Helpers\MakeControllerCommand;
use Vinexel\Modules\Commands\Helpers\MakeModelCommand;
use Vinexel\Modules\Commands\Helpers\MakeMigrateCommand;
use Vinexel\Modules\Commands\Helpers\MakeSeedCommand;
use Vinexel\Modules\Commands\Helpers\MigrateCommand;
use Vinexel\Modules\Commands\Helpers\SeedCommand;
use Vinexel\Modules\Commands\Helpers\RollbackCommand;
use Vinexel\Modules\Commands\Helpers\ShowCommand;
use Vinexel\Modules\Commands\Helpers\MakeProjectCommand;
use Vinexel\Modules\Commands\Helpers\DeleteProjectCommand;

/**
 * Command Registry
 *
 * The `Registry` class defines a centralized list of all available
 * CLI commands for the Vinexel Framework. Each command keyword is
 * mapped to its corresponding command class.
 *
 * This structure allows the command handler to easily locate and
 * execute the appropriate command when a user runs a CLI instruction.
 */
class Registry
{
    /**
     * Returns an associative array of all registered CLI commands.
     *
     * The key represents the CLI command name (e.g., `make:model`),
     * while the value is the fully qualified class name responsible
     * for executing that command.
     *
     * @return array<string, string> List of CLI command mappings.
     */
    public static function getCommands()
    {
        return [
            'serve' => ServeCommand::class,
            'make:controller' => MakeControllerCommand::class,
            'make:model' => MakeModelCommand::class,
            'make:migrate' => MakeMigrateCommand::class,
            'make:seed' => MakeSeedCommand::class,
            'migration' => MigrateCommand::class,
            'seeder' => SeedCommand::class,
            'rollback' => RollbackCommand::class,
            'list' => ShowCommand::class,
            'make:project' => MakeProjectCommand::class,
            'delete' => DeleteProjectCommand::class,
        ];
    }
}
