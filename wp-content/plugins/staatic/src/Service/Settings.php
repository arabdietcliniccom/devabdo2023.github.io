<?php

declare(strict_types=1);

namespace Staatic\WordPress\Service;

use InvalidArgumentException;
use RuntimeException;
use Staatic\WordPress\Setting\ActsOnUpdateInterface;
use Staatic\WordPress\Setting\ComposedSettingInterface;
use Staatic\WordPress\Setting\RendersPartialsInterface;
use Staatic\WordPress\Setting\SettingInterface;
use Staatic\WordPress\Setting\StoresEncryptedInterface;
use Staatic\WordPress\SettingGroup\SettingGroupInterface;

final class Settings
{
    const ENCRYPTION_CIPHER = 'aes-256-cbc';

    /** @var SettingGroupInterface[] */
    private $groups = [];

    /** @var SettingInterface[] */
    private $settings = [];

    /**
     * @var mixed[]
     */
    private $settingsToGroups = [];

    /**
     * @var PartialRenderer
     */
    private $renderer;

    public function __construct(PartialRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @return void
     */
    public function addGroup(SettingGroupInterface $group)
    {
        // Allow replace, so no checking whether group exists...
        $this->groups[$group->name()] = $group;
        \uasort($this->groups, function (SettingGroupInterface $a, SettingGroupInterface $b) {
            return $a->position() <=> $b->position();
        });
    }

    /**
     * @return void
     */
    public function addSetting(string $groupName, SettingInterface $setting)
    {
        $this->settings[$setting->name()] = $setting;
        $this->settingsToGroups[$setting->name()] = $groupName;
    }

    /**
     * @return void
     */
    public function registerSettings()
    {
        foreach ($this->settings as $settingName => $setting) {
            $groupName = $this->settingsToGroups[$settingName];
            if ($setting instanceof RendersPartialsInterface) {
                $setting->setPartialRenderer($this->renderer);
            }
            if ($setting instanceof ComposedSettingInterface) {
                $settings = $setting->settings();
            } else {
                $settings = [$setting];
            }
            foreach ($settings as $innerSetting) {
                if ($innerSetting instanceof RendersPartialsInterface) {
                    $innerSetting->setPartialRenderer($this->renderer);
                }
                \register_setting($groupName, $innerSetting->name(), [
                    'type' => $innerSetting->type(),
                    'description' => $innerSetting->description(),
                    'sanitize_callback' => [$innerSetting, 'sanitizeValue'],
                    'default' => $innerSetting->defaultValue()
                ]);
                if ($innerSetting instanceof StoresEncryptedInterface) {
                    \add_filter("option_{$innerSetting->name()}", function ($value) {
                        return $this->decryptOptionValue($value);
                    }, \PHP_INT_MIN);
                    \add_filter("pre_update_option_{$innerSetting->name()}", function ($value) {
                        return $this->encryptOptionValue($value);
                    }, \PHP_INT_MAX);
                }
                if ($innerSetting instanceof ActsOnUpdateInterface) {
                    \add_action("add_option_{$innerSetting->name()}", function ($option, $value) use ($innerSetting) {
                        if ($innerSetting instanceof StoresEncryptedInterface) {
                            $value = $this->decryptOptionValue($value);
                        }
                        $innerSetting->onUpdate($value, null);
                    }, 10, 2);
                    \add_action("update_option_{$innerSetting->name()}", function ($oldValue, $value, $option) use (
                        $innerSetting
                    ) {
                        if ($innerSetting instanceof StoresEncryptedInterface) {
                            $value = $this->decryptOptionValue($value);
                        }
                        $innerSetting->onUpdate($value, $oldValue);
                    }, 10, 3);
                }
            }
        }
    }

    private function decryptOptionValue($value)
    {
        if (empty($value)) {
            return $value;
        }
        $payload = \json_decode(\base64_decode($value), \true);
        if (!\is_array($payload)) {
            // The option value could be unencrypted (legacy option).
            // Simply return the original option value.
            return $value;
        }
        if (!isset($payload['iv'], $payload['value'])) {
            throw new RuntimeException('Unable to decrypt value.');
        }
        $result = \openssl_decrypt(
            $payload['value'],
            self::ENCRYPTION_CIPHER,
            $this->encryptionKey(),
            0,
            \base64_decode($payload['iv'])
        );
        if ($result === \false) {
            throw new RuntimeException('Unable to decrypt value.');
        }

        return $result;
    }

    private function encryptOptionValue($value) : string
    {
        $iv = \random_bytes(\openssl_cipher_iv_length(self::ENCRYPTION_CIPHER));
        $value = \openssl_encrypt($value, self::ENCRYPTION_CIPHER, $this->encryptionKey(), 0, $iv);
        if ($value === \false) {
            throw new RuntimeException('Unable to encrypt value.');
        }
        $json = \json_encode([
            'iv' => \base64_encode($iv),
            'value' => $value
        ], \JSON_UNESCAPED_SLASHES);
        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new RuntimeException('Unable to encrypt value.');
        }

        return \base64_encode($json);
    }

    private function encryptionKey() : string
    {
        if (\defined('Staatic\\Vendor\\STAATIC_KEY')) {
            return \constant('STAATIC_KEY');
        }

        return \constant('AUTH_KEY');
    }

    /**
     * @return SettingGroupInterface[]
     */
    public function groups() : array
    {
        return $this->groups;
    }

    public function group(string $name) : SettingGroupInterface
    {
        if (!isset($this->groups[$name])) {
            throw new InvalidArgumentException("Setting group '{$name}' does not exist");
        }

        return $this->groups[$name];
    }

    /**
     * @return SettingInterface[]
     * @param string|null $groupName
     */
    public function settings($groupName = null) : array
    {
        $settings = $this->settings;
        if ($groupName) {
            $settings = \array_filter($settings, function (SettingInterface $setting) use ($groupName) {
                return $this->settingsToGroups[$setting->name()] === $groupName;
            });
        }

        return $settings;
    }

    /**
     * @return void
     */
    public function settingsApiInit()
    {
        foreach ($this->groups as $groupName => $group) {
            $groupPageId = \sprintf('%s-settings-page', $groupName);
            $groupSectionId = \sprintf('%s-settings-section', $groupName);
            \add_settings_section(
                $groupSectionId,
                '',
                // $groupLabel,
                [$group, 'render'],
                $groupPageId
            );
            foreach ($this->settings($groupName) as $setting) {
                if (!$setting->isEnabled()) {
                    continue;
                }
                \add_settings_field(
                    $setting->name(),
                    $setting->label(),
                    [$setting, 'render'],
                    $groupPageId,
                    $groupSectionId,
                    [
                    'class' => \sprintf('%s %s', $groupName, $setting->name())
                
                ]);
            }
        }
    }

    public function renderErrors() : string
    {
        \ob_start();
        \settings_errors('staatic-settings');
        $errors = \ob_get_clean();

        return $errors;
    }

    public function renderHiddenFields(string $groupName) : string
    {
        \ob_start();
        \settings_fields($groupName);
        $hiddenFields = \ob_get_clean();

        return $hiddenFields;
    }

    public function renderSettings(string $groupName) : string
    {
        $groupPageId = \sprintf('%s-settings-page', $groupName);
        \ob_start();
        \do_settings_sections($groupPageId);
        $settings = \ob_get_clean();

        return $settings;
    }

    public function hasSettings(string $groupName) : bool
    {
        $groupsWithSettings = \array_unique(\array_values($this->settingsToGroups));

        return \in_array($groupName, $groupsWithSettings);
    }
}
