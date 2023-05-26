<?php

namespace ChaosWD\Controller;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class VariableController
{
    protected static $testCases = ['env', 'ini', 'conf'];
    protected static $rootDirectory = "/";

    public static function process(string $rootDirectory = "/")
    {
        foreach (self::$testCases as $case) {
            $files = self::searchDirectory($case, $rootDirectory);

            foreach ($files as $file) {
                self::convertVars($file);
            }
        }
    }

    public static function searchDirectory($targetExtension, $rootDirectory)
    {
        $dirIterator = new RecursiveDirectoryIterator($rootDirectory);
        $iterator = new RecursiveIteratorIterator($dirIterator, RecursiveIteratorIterator::SELF_FIRST);

        $matchingFiles = [];

        foreach ($iterator as $file) {
            if (!$file->isDir()) {
                $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

                if ($extension === $targetExtension) {
                    $matchingFiles[] = $file->getPathname();
                }
            }
        }

        return $matchingFiles;
    }


    public static function convertVars($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'env':
                self::env($file);
                break;
            case 'ini':
            case 'conf':
                self::ini($file);
                break;
        }

        return;
    }

    private static function env($file)
    {
        $content = file_get_contents($file);
        $envLines = explode("\n", $content);
        $envVariables = [];

        foreach ($envLines as $line) {
            $line = str_replace(" ", "", $line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2);
            $value = str_replace("'", '', $value);
            $value = str_replace('"', '', $value);
            $envVariables[$key] = $value;
        }

        foreach ($envVariables as $key => $value) {
            if (isset($_ENV[$key])) {
                $key = self::duplicateKey($key);
            }
            $_ENV[$key] = $value;
        }

        return;
    }

    private static function ini($file)
    {
        $array = parse_ini_file($file, true, INI_SCANNER_TYPED);

        foreach ($array as $section => $values) {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    $flattenedKey = "{$section}_{$key}";
                    self::flattenArray($flattenedKey, $value);
                } elseif (isset($_ENV[$key])) {
                    $key = self::duplicateKey($key);
                }
                $_ENV[$key] = $value;
            }
        }
    }

    private static function flattenArray($prefix, $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                self::flattenArray("{$prefix}_{$key}", $value);
            } else {
                $_ENV["{$prefix}_{$key}"] = $value;
            }
        }
    }

    private static function duplicateKey($key)
    {
        if (substr($key, -1) == (int) substr($key, -1)) {
            while (isset($_ENV[$key])) {
                $id = substr($key, -1);
                $id++;

                $length = strlen($key);
                $key = substr($key, 0, $length - 1);

                $key .= $id;
            }
        } else {
            $key .= "1";
        }
        return $key;
    }
}
