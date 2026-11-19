<?php
/**
 * Shared configuration loader for the Agriculture Portal.
 *
 * Reads key=value pairs from the project root .env file (if present) and
 * exposes small helpers used by the rest of the application. Environment
 * variables that already exist on the server always win over .env values.
 */

if (!function_exists('agri_load_env_file')) {
    function agri_load_env_file(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $index => $line) {
            if ($index === 0) {
                $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
            }

            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);
            if ($name === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
                continue;
            }

            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = substr($value, -1);
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            $existing = getenv($name);
            if ($existing === false || $existing === '') {
                putenv($name . '=' . $value);
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

agri_load_env_file(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');

if (!function_exists('agri_env')) {
    function agri_env(string $name, ?string $default = null): ?string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return $default;
        }
        return $value;
    }
}

if (!function_exists('agri_env_bool')) {
    function agri_env_bool(string $name, bool $default = false): bool
    {
        $value = agri_env($name);
        if ($value === null) {
            return $default;
        }
        return in_array(strtolower(trim($value)), array('1', 'true', 'yes', 'on'), true);
    }
}

date_default_timezone_set(agri_env('APP_TIMEZONE', 'Asia/Kolkata'));
