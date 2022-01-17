<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @link https://stackoverflow.com/a/68471990
 */
class StreamedCsvResponse extends StreamedResponse
{
    private string $filename;
    private string $separator = ',';
    private string $enclosure = '"';
    private array $data;
    private array $fieldNames;

    public function __construct(
        array $data,
        array $fieldNames = [],
        ?string $filename = null,
        string $separator = ',',
        string $enclosure = '"',
        $status = 200,
        $headers = []
    ) {
        $this->data = $data;
        $this->fieldNames = $fieldNames;
        $this->enclosure = $enclosure;
        $this->separator = $separator;
        if (null === $filename) {
            $filename = uniqid() . '.csv';
        }

        if (!str_ends_with($filename, '.csv')) {
            $filename .= '.csv';
        }

        $this->filename = $filename;

        parent::__construct([$this, 'stream'], $status, $headers);
//        $this->setHeaders();
    }

    private function setHeaders(): void
    {
        $this->headers->set(
            'Content-disposition',
            HeaderUtils::makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $this->filename)
        );

        if (!$this->headers->has('Content-Type')) {
            $this->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        }

        if (!$this->headers->has('Content-Encoding')) {
            $this->headers->set('Content-Encoding', 'UTF-8');
        }
    }

    public function stream(): void
    {
        $handle = fopen('php://temp', 'r+b');

        $this->encode($this->data, $handle);

        if (!is_resource($handle)) {
            return;
        }

        rewind($handle);

        while ($t = stream_get_contents($handle, 1024)) {
            echo $t;
        }

        fclose($handle);
    }

    private function encode(array $data, $handle): void
    {
        if (!is_resource($handle)) {
            return;
        }

        // fieldnames?
        if ( $this->fieldNames ) {
            $defaultRow = array_fill_keys($this->fieldNames, null);
            fputcsv($handle, $this->fieldNames, $this->separator, $this->enclosure);

            foreach ($data as $row) {
                $csvRow = array_intersect_key($row, $defaultRow);
                $csvRow = array_merge($defaultRow, $csvRow);

                try {
                    fputcsv($handle, $csvRow, $this->separator, $this->enclosure);
                } catch (\Exception $E) {
                    print_r($row);
                }
            }
        } else {
            foreach ($data as $row) {
                fputcsv($handle, $row, $this->separator, $this->enclosure);
            }
        }

    }
}
