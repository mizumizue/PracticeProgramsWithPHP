<?php

/**
 * CSVを操作するクラス
 */
class CsvManipulte
{

    /**
     * CSVファイルを連想配列に変換する
     * @param string $csvFile
     * @param string $encodingTo
     * @param string $encodingFrom
     * @return array $data
     */
    public static function convertCsvToArray($csvFile, $encodingTo = null, $encodingFrom = null)
    {
        $readFile = null;
        if (!is_null($encodingTo) && !is_null($encodingFrom)) {
            $fileContent = file_get_contents($csvFile);
            $convertedContent = mb_convert_encoding($fileContent, $encodingTo, $encodingFrom);
            $tempFile = tmpfile();
            $metaData = stream_get_meta_data($tempFile);
            fwrite($tempFile, $convertedContent);
            rewind($tempFile);
            $readFile = $metaData['uri'];
        }
        $readFile = is_null($readFile) ? $csvFile : $readFile;
        $splFileObject = new SplFileObject($readFile);
        $splFileObject->setFlags(SplFileObject::READ_CSV);
        $data = [];
        $headerLine = $splFileObject->fgetcsv();
        foreach ($splFileObject as $lineNum => $line) {
            $convertedLine = [];
            foreach ($headerLine as $column => $name) {
                $value = isset($line[$column]) ? $line[$column] : null;
                $convertedLine[$name] = $value;
            }
            $data[] = $convertedLine;
        }
        if (isset($tempFile)) {
            fclose($tempFile);
        }
        $splFileObject = null;
        return $data;
    }

    /**
     * 連想配列をCSV変換して吐き出す
     * @param string $newFile
     * @param array $data
     * @param string $encodingTo
     * @param string $encodingFrom
     * @return bool
     * @throws RuntimeException
     */
    public static function convertArrayToCsv($newFile, $data, $encodingTo = null, $encodingFrom = null)
    {
        if (!is_null($encodingTo) && !is_null($encodingFrom)) {
            mb_convert_variables($encodingTo, $encodingFrom, $data);
        }
        $splFileObject = new SplFileObject($newFile, 'w');
        foreach ($data as $i => $fields) {
            if (!$splFileObject->fputcsv($fields)) {
                new RuntimeException("Write Csv Error. RowNum: {$i}.");
            }
        }
        return true;
    }
}
