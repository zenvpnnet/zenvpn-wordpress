<?php

namespace zenVPN;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Autoloader class.
 *
 * WP Coding Standards compliant autoloader.
 *
 */
final class Autoloader
{

    /**
     * @var string Project-specific namespace prefix.
     */
    const PREFIX = 'zenVPN';

    /**
     * @var string Base directory for the namespace prefix.
     */
    const BASE_DIR = __DIR__ . DIRECTORY_SEPARATOR . 'src';

    /**
     * Register loader.
     *
     * @link https://www.php.net/manual/en/function.spl-autoload-register.php
     */
    public function register()
    {
        spl_autoload_register(array($this, 'load_class'));
    }

    /**
     * Check whether the given class name uses the namespace prefix.
     *
     * @param string $class The class name to check.
     * @return bool
     */
    private function starts_with_namespace_prefix(string $class)
    {
        $len = strlen(self::PREFIX);
        return strncmp(self::PREFIX, $class, $len) === 0;
    }

    /**
     * Return the mapped file for the namespace prefix and the given class name.
     *
     * Replace the namespace prefix with the base directory,
     * replace namespace separators with directory separators,
     * and append with `.php`.
     *
     * @param string $class The fully-qualified class name.
     * @return string
     */
    private function get_mapped_file(string $class)
    {
        $relative_class = substr($class, strlen(self::PREFIX));
        // Split the class name by backslashes into an array
        $parts = explode('\\', $relative_class);
        // Get the first element as the subdirectory name
        $subdir = array_shift($parts);
        // Convert the remaining elements to lowercase and replace underscores with hyphens
        $parts = array_map(function ($part) {
            return strtolower(str_replace('_', '-', $part));
        }, $parts);
        // Check if the last element is an interface
        if (strpos(end($parts), '-interface') === false) {
            // Prepend 'class-' to the file name
            $parts[count($parts) - 1] = 'class-' . $parts[count($parts) - 1];
        }
        // Join the elements with slashes
        $relative_class = implode(DIRECTORY_SEPARATOR, $parts);
        // Return the full path with the base directory and the subdirectory
        return self::BASE_DIR . $subdir . DIRECTORY_SEPARATOR . $relative_class . '.php';
    }

    /**
     * Require the file at the given path, if it exists.
     *
     * @param string $file
     */
    private function require_file(string $file)
    {
        if (file_exists($file)) {
            require $file;
        }
    }

    /**
     * Load the class file for the given class name.
     *
     * @param string $class The fully-qualified class name.
     */
    public function load_class(string $class)
    {
        if (!$this->starts_with_namespace_prefix($class)) {
            /*
            * Class does not use the namespace prefix,
            * move to the next registered autoloader.
            */
            return;
        }

        $mapped_file = $this->get_mapped_file($class);
        $this->require_file($mapped_file);
    }

}