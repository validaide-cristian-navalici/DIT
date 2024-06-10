<?php declare(strict_types=1);

namespace Dit;

use DateTime;
use Exception;
use PDO;
use PDOException;

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
        private readonly string $dbPass,
        private readonly string $host
    )
    {
        $this->dsn     = sprintf("mysql:host=%s;dbname=%s;charset=utf8", $host, $this->dbName);
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

            $this->display(sprintf("Generated file: %s\n", $generatedFile));
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
        $this->display("Grabbing all the tables...\n", true);

        $conn = $this->createConnection();

        $query  = $conn->query("SHOW TABLES");
        $result = $query->fetchAll(PDO::FETCH_NUM);

        return array_merge(...$result);
    }

    private function generateChecksums(array $tables): array
    {
        $this->display("Generating checksums...\n");

        $conn      = $this->createConnection();
        $checksums = [];

        foreach ($tables as $table) {
            $query  = $conn->query(sprintf("CHECKSUM TABLE %s", $table));
            $result = $query->fetchColumn(1);

            $this->display(sprintf("Parsing table: %-50s -> %-12s\n", $table, $result), true);

            $checksums[$table] = $result;
        }

        $this->display("Done.\n");

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

    private function display(string $text, bool $verboseAware = false): void
    {
        if (($verboseAware && $this->verboseMode) || (!$verboseAware)) {
            printf($text);
        }
    }
}
