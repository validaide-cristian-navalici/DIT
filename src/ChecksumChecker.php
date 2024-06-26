<?php declare(strict_types=1);

namespace Dit;

class ChecksumChecker
{
    public function compare(string $firstFilename, string $secondFilename): void
    {
        $firstData  = json_decode(file_get_contents($firstFilename), true);
        $secondData = json_decode(file_get_contents($secondFilename), true);

        $onlyInFirst = array_diff_key($firstData, $secondData);
        $onlyInSecond = array_diff_key($secondData, $firstData);

        $this->displayDiffTables($onlyInFirst, $firstFilename);
        $this->displayDiffTables($onlyInSecond, $secondFilename);

        $this->displayDiffContent($firstData, $secondData);
    }

    private function displayDiffTables(array $content, string $filename): void
    {
        if ($content) {
            printf("The following records exists only in %s\n", $filename);
            foreach($content as $table => $checksum) {
                printf("TABLE: %-50s CHECKSUM: %s\n", $table, $checksum);
            }
            printf("\n");
        }
    }

    private function displayDiffContent(array $firstData, array $secondData): void
    {
        printf("CHECKSUMS Differences \n");
        $hasAnyDiff = false;

        foreach($firstData as $firstKey => $firstSum) {
            $secondSum = $secondData[$firstKey] ?? null;
            if ($secondSum && ($firstSum != $secondSum)) {
                printf("%-50s %12s <> %12s\n", $firstKey, $firstSum, $secondSum);
                $hasAnyDiff = true;
            }
        }

        if (!$hasAnyDiff) {
            printf("All things look good, no differences found.\n");
        }
    }
}
