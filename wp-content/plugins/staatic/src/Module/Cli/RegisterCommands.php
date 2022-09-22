<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module\Cli;

use Staatic\WordPress\Cli\PublishCommand;
use Staatic\WordPress\Module\ModuleInterface;
use WP_CLI;

final class RegisterCommands implements ModuleInterface
{
    /**
     * @var PublishCommand
     */
    private $publishCommand;

    public function __construct(PublishCommand $publishCommand)
    {
        $this->publishCommand = $publishCommand;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        if (!\defined('WP_CLI') || !\constant('WP_CLI')) {
            return;
        }
        WP_CLI::add_command('staatic', $this->publishCommand);
    }
}
