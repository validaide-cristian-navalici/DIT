#!/usr/bin/env php
<?php

use Dit\ChecksumChecker;
use Dit\ChecksumGenerator;

require_once 'vendor/autoload.php';


$parser = new Console_CommandLine([
    'description' => 'DIT - Database Integrity Tool',
    'version'     => '1.0.0',
]);

$parser->addCommand('generateChecksum', ['description' => 'Generate a checksum for all the tables in a MySQL DB']);
$parser->addOption('verbose', [
    'short_name'  => '-v',
    'long_name'   => '--verbose',
    'action'      => 'StoreTrue',
    'description' => 'Enable verbose output',
]);

$compareCmd = $parser->addCommand(
    'compareChecksum',
    ['description' => 'Compares the checksums between two previously generated files']
);
$compareCmd->addArgument('firstFile', ['description' => 'First checksum file']);
$compareCmd->addArgument('secondFile', ['description' => 'Second checksum file']);


try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    printf("ERROR: %s\n", $e->getMessage());
    exit(1);
}

try {
    $result      = $parser->parse();
    $verboseFlag = $result->options['verbose'] ?? false;

    switch ($result->command_name) {
        case 'generateChecksum':
            $generator = new ChecksumGenerator($_ENV['DB_NAME'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $_ENV['DB_HOST']);
            $generator->setVerbose($verboseFlag);
            $generator->generate();
            break;
        case 'compareChecksum':
            $first  = $result->command->args['firstFile'] ?? '';
            $second = $result->command->args['secondFile'] ?? '';

            $checker = new ChecksumChecker();
            $checker->compare($first, $second);
            break;
        default:
            $parser->displayUsage(1);
    }
} catch (Exception $exc) {
    $parser->displayError($exc->getMessage());
}
