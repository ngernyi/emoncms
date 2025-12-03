<?php

class Config
{
    /**
     * @var array|false Holds the configuration settings.
     */
    private static $settings = false;

    /**
     * Loads configuration from the settings.ini file.
     *
     * @param string $path The full path to the settings.ini file.
     */
    public static function load($path)
    {
        if (file_exists($path)) {
            self::$settings = parse_ini_file($path, true, INI_SCANNER_TYPED);
        }
    }

    /**
     * Get a configuration value.
     *
     * Checks for an environment variable first, then falls back to the INI file.
     * Environment variable names are derived from the section and key, e.g., [sql] server -> SQL_SERVER.
     *
     * @param string $section The section in the INI file (e.g., 'sql').
     * @param string|null $key The key within the section (e.g., 'server').
     * @param mixed|null $default The default value to return if not found.
     * @return mixed The configuration value.
     */
    public static function get($section, $key = null, $default = null)
    {
        // If no key is provided, return the entire section.
        if ($key === null) {
            if (isset(self::$settings[$section])) return self::$settings[$section];
            return $default;
        }

        // 1. Check for environment variable
        $env_key = strtoupper($section . '_' . $key);
        $env_value = getenv($env_key);
        if ($env_value !== false) {
            // Basic type casting for env vars which are always strings
            if (is_numeric($env_value)) return $env_value + 0; // Convert to int/float
            if (in_array(strtolower($env_value), ['true', 'false'])) return strtolower($env_value) === 'true';
            return $env_value;
        }

        // 2. Check for setting in the INI file
        if (isset(self::$settings[$section][$key])) {
            return self::$settings[$section][$key];
        }

        // 3. Return default value
        return $default;
    }

    /**
     * Get a boolean configuration value.
     *
     * @param string $section
     * @param string $key
     * @param bool $default
     * @return bool
     */
    public static function get_bool($section, $key, $default = false)
    {
        $value = self::get($section, $key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Checks if the configuration has been loaded.
     *
     * @return bool
     */
    public static function is_loaded()
    {
        return self::$settings !== false;
    }
}