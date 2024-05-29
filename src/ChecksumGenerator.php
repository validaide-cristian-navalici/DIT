<?php declare(strict_types=1);

namespace Dit;

use DateTime;
use PDO;
use PDOException;
use Exception;

class ChecksumGenerator
{
    private const OUTPUT_CHECKSUM_NAME = 'checksums';

    private string $dsn;
    private array  $options;
    private ?PDO   $conn        = null;
    private bool   $verboseMode = false;

    public function __construct(
        private readonly string $dbName,
        private readonly string $dbUser,
        private readonly string $dbPass
    )
    {
        $this->dsn     = sprintf("mysql:host=localhost;dbname=%s;charset=utf8", $this->dbName);
        $this->options = [
            PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }

    public function generate(): void
    {
        try {
            $allTables     = $this->getAllTables();
            $checksums     = $this->generateChecksums($allTables);
            $generatedFile = $this->output($checksums);

            printf("Generated file: %s\n", $generatedFile);
        } catch (Exception $e) {
            echo "ERROR: %s" . $e->getMessage();
            exit(1);
        }
    }

    public function setVerbose(bool $flagValue): void
    {
        $this->verboseMode = $flagValue;
    }


    private function getAllTables(): array
    {
        if ($this->verboseMode) {
            echo sprintf("Grabbing all the tables...\n");
        }

        $conn = $this->createConnection();

        $query  = $conn->query("SHOW TABLES");
        $result = $query->fetchAll(PDO::FETCH_NUM);

        return array_merge(...$result);
    }

    private function generateChecksums(array $tables): array
    {
        if ($this->verboseMode) {
            echo sprintf("Generating checksums...\n");
        }

        $conn      = $this->createConnection();
        $checksums = [];

        foreach ($tables as $table) {
            $query  = $conn->query(sprintf("CHECKSUM TABLE %s", $table));
            $result = $query->fetchColumn(1);

            if ($this->verboseMode) {
                echo sprintf("Parsing table: %s -> %s\n", $table, $result);
            }

            $checksums[$table] = $result;
        }

        return $checksums;
    }

    private function output(array $content): string
    {
        $outputFilename = sprintf("%s_%s.json", self::OUTPUT_CHECKSUM_NAME, (new DateTime('now'))->getTimestamp());

        file_put_contents($outputFilename, json_encode($content));

        return $outputFilename;
    }

    private function createConnection(): PDO
    {
        try {
            if ($this->conn) {
                return $this->conn;
            }

            $this->conn = new PDO($this->dsn, $this->dbUser, $this->dbPass, $this->options);

            return $this->conn;
        } catch (PDOException $e) {
            printf("ERROR: %s", $e->getMessage());
            exit(1);
        }
    }
}
