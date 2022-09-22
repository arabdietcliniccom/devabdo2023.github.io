<?php

declare(strict_types=1);

namespace Staatic\WordPress\Module;

use Staatic\WordPress\Migrations\MigrationCoordinatorFactory;
use WP_Error;

final class EnsureMigrated implements ModuleInterface
{
    const NAMESPACE = 'staatic';

    /**
     * @var MigrationCoordinatorFactory
     */
    private $coordinatorFactory;

    public function __construct(MigrationCoordinatorFactory $coordinatorFactory)
    {
        $this->coordinatorFactory = $coordinatorFactory;
    }

    /**
     * @return void
     */
    public function hooks()
    {
        if (!\is_admin()) {
            return;
        }
        \add_action('init', [$this, 'migrator'], 100);
    }

    /**
     * @return void
     */
    public function migrator()
    {
        $migrationsDirectory = __DIR__ . '/../../migrations';
        $coordinator = ($this->coordinatorFactory)(self::NAMESPACE, $migrationsDirectory);
        if ($coordinator->shouldMigrate()) {
            $coordinator->migrate();
        } elseif ($coordinator->isMigrating()) {
            \wp_die(
                new WP_Error(
                    'locked',
                    \__('The Staatic database is being upgraded; please try again later.', 'staatic')
                )
            );
        }
    }

    public static function getDefaultPriority() : int
    {
        return \PHP_INT_MAX;
    }
}
