<?php

namespace App\Helpers;

use App\Models\AccidentProgress;
use Illuminate\Http\UploadedFile;

class FileHelper
{
    const CSV_MAX_LINE_LENGTH = 2000;

    /**
     * csvToArray
     *
     * @param  UploadedFile $file
     * @param  string $delimiter
     * @return array
     */
    public static function csvToArray(UploadedFile $file, string $delimiter = ','): array | bool
    {
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }

        $header = null;
        $data = [];
        $invalidRows = 0;
        $columnDate = [
            // User Contract
            "生年月日",
            "更新日",
            "始期",
            "終期",
            "変更登録・解約日",
            "変更事由＿１",
            // Accident
            '事故日',
        ];
        $columnInvalidText = [
            // Accident
            '補償種類' => AccidentProgress::COVERAGE_TYPE,
            '対人進捗' => AccidentProgress::PERSONAL_PROGRESS,
            '対物進捗' => AccidentProgress::PROPERTY_DAMAGE_PROGRESS,
        ];
        if (($handle = fopen($file, 'r')) !== false) {
            while (($row = fgetcsv($handle, self::CSV_MAX_LINE_LENGTH, $delimiter)) !== false) {
                if (!$header) {
                    $header = array_filter($row, fn($value) => $value);
                } else {
                    // Filter out empty cells
                    $hasFullHash = false;
                    $row = array_map(function ($index) use ($row, &$hasFullHash, $columnDate, $header, $columnInvalidText) {
                        $value = !empty($row[$index]) ? $row[$index] : null;
                        $colName = $header[$index] ?? null;
                        if (!is_null($value)) {
                            // Check date
                            if (in_array($colName, $columnDate) && self::checkDateValue($value)) {
                                $hasFullHash = true;
                            }
                            // Check invalid text
                            if (array_key_exists($colName, $columnInvalidText) &&
                                !self::checkInvalidText($value, $columnInvalidText[$colName])
                            ) {
                                $hasFullHash = true;
                            }
                        }
                        return $value;
                    }, array_keys($header));

                    if ($hasFullHash) {
                        $invalidRows++;
                        continue;
                    }

                    $rowWithIndexKey = [];
                    foreach (array_values($row) as $index => $value) {
                        $rowWithIndexKey[$index] = $value;
                    }
                    $data[] = $rowWithIndexKey;
                }
            }
            fclose($handle);
        }

        return [
            'data' => $data,
            'invalid_rows' => $invalidRows,
        ];
    }

    /**
     * checkDateValue
     *
     * @param  string $value
     * @return bool
     */
    public static function checkDateValue(string $value): bool
    {
        // Validate string contains only "#"
        return !is_null($value) && preg_match('/^#+$/', trim($value));
    }

    /**
     * checkInvalidText
     *
     * @param  string $value
     * @param  array $array
     * @return bool
     */
    public static function checkInvalidText(string $value, array $array): bool
    {
        return array_key_exists($value, $array);
    }
}
