<?php

declare(strict_types=1);

namespace Staatic\WordPress\Migrations;

use Exception;
use wpdb;

final class MigrationCoordinator
{
    const MIGRATION_OPTION_NAME = '%s_database_version';

    /**
     * @var mixed[]|null
     */
    private $status;

    /**
     * @var Migrator
     */
    private $migrator;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $targetVersion;

    /**
     * @var \wpdb
     */
    private $wpdb;

    public function __construct(Migrator $migrator, string $namespace, string $targetVersion, wpdb $wpdb)
    {
        $this->migrator = $migrator;
        $this->namespace = $namespace;
        $this->targetVersion = $targetVersion;
        $this->wpdb = $wpdb;
    }

    public function status() : array
    {
        if ($this->status === null) {
            $status = \get_option($this->optionName());
            if (\is_string($status) && !empty($status)) {
                $this->status = [
                    'version' => $status
                ];
            } elseif (!\is_array($status) || !isset($status['version'])) {
                $this->status = [
                    'version' => '0.0.0'
                ];
            } else {
                $this->status = $status;
            }
        }

        return $this->status;
    }

    public function isMigrating() : bool
    {
        $status = $this->status();
        if (!\array_key_exists('lock', $status)) {
            return \false;
        }

        return \strtotime('-30 minutes') <= $status['lock'];
    }

    public function shouldMigrate() : bool
    {
        $status = $this->status();
        if ($this->isMigrating()) {
            return \false;
        }

        return \version_compare($status['version'], $this->targetVersion, '<');
    }

    public function migrate() : bool
    {
        if (!$this->lockMigration()) {
            return \false;
        }
        $status = $this->status();
        $installedVersion = $status['version'];

        try {
            $this->migrator->migrate($this->targetVersion, $installedVersion);
            $this->clearTransientCache();
        } catch (Exception $e) {
            $this->migrationFailed($e->getMessage());

            return \false;
        }
        $this->migrationSuccessful();

        return \true;
    }

    /**
     * @return void
     */
    private function clearTransientCache()
    {
        $this->wpdb->query(
            $this->wpdb->prepare("DELETE FROM {$this->wpdb->prefix}options WHERE option_name LIKE %s", $this->wpdb->esc_like(
                '_transient_staatic_'
            ) . '%')
        );
    }

    /**
     * @return void
     */
    private function migrationSuccessful()
    {
        $status = $this->status();
        unset($status['lock'], $status['error']);
        $status['version'] = $this->targetVersion;
        $this->setStatus($status);
    }

    /**
     * @return void
     */
    private function migrationFailed(string $message)
    {
        $status = $this->status();
        $status['error'] = [
            'time' => \time(),
            'version' => $this->targetVersion,
            'message' => $message
        ];
        $this->setStatus($status);
    }

    private function lockMigration() : bool
    {
        $status = $this->status();
        $status['lock'] = \time();

        return $this->setStatus($status);
    }

    private function setStatus($status) : bool
    {
        $this->status = $status;

        return \update_option($this->optionName(), $status);
    }

    private function optionName() : string
    {
        return \sprintf(self::MIGRATION_OPTION_NAME, $this->namespace);
    }
}
