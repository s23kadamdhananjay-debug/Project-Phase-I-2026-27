<?php
/**
 * Small helper that runs the Python machine-learning scripts.
 *
 * Every argument is escaped individually, the scripts are addressed by an
 * absolute path, and stderr is captured so a missing package or bad input is
 * reported as a readable message instead of a blank result.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'config.php';

if (!function_exists('agri_run_ml')) {
    /**
     * @param string $relativeScript Path inside the farmer/ folder.
     * @param array  $args           Plain string arguments.
     * @param string $error          Filled with a user friendly error message.
     * @return string|null Trimmed stdout, or null on failure.
     */
    function agri_run_ml(string $relativeScript, array $args, ?string &$error = null): ?string
    {
        $error = null;
        $root = dirname(__DIR__);
        $script = $root . DIRECTORY_SEPARATOR . 'farmer' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativeScript);

        if (!is_file($script)) {
            $error = 'The prediction script is missing: ' . $relativeScript;
            return null;
        }

        $python = agri_env('PYTHON', 'python');
        if ($python === null || trim($python) === '') {
            $python = 'python';
        }

        $commandParts = array(escapeshellarg($python), escapeshellarg($script));
        foreach ($args as $arg) {
            $commandParts[] = escapeshellarg((string)$arg);
        }
        $command = implode(' ', $commandParts) . ' 2>&1';

        $output = shell_exec($command);
        if ($output === null) {
            $error = 'Python could not be started. Install Python and the packages from farmer/requirements.txt, then set PYTHON in .env if needed.';
            return null;
        }

        $output = trim($output);
        if ($output === '') {
            $error = 'The prediction script returned no output. Check that Python and the required packages are installed.';
            return null;
        }

        if (preg_match('/(Traceback \(most recent call last\)|ModuleNotFoundError|ImportError|SyntaxError|ValueError|FileNotFoundError)/i', $output)) {
            error_log('ML script error (' . $relativeScript . '): ' . $output);
            $error = 'The prediction could not be completed. Make sure the Python packages are installed (pip install -r farmer/requirements.txt).';
            return null;
        }

        return $output;
    }
}
