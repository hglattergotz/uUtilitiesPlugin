<?php
/**
 * @package     uUtilitiesPlugin
 * @subpackage  uCsvWriter
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uCsvWriter
{
  protected $fp;

  public function __construct()
  {
    $mem = 5*1024*1024;
    $this->fp = fopen ('php://temp/maxmemory:'.$mem, 'r+');
  }

  public function __destruct()
  {
    if (false !== $this->fp)
    {
      fclose($this->fp);
    }
  }

  public function writeRow(array $row, $throwException = true)
  {
    $r = fputcsv($this->fp, $row);

    if (false === $r && $throwException)
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|Error wile calling fputcsv');
    }

    return $r;
  }

  public function writeRows(array $rows, $throwException = true)
  {
    foreach ($rows as $row)
    {
      if (false === $this->writeRow($row, $throwException))
      {
        return false;
      }
    }

    return count($rows);
  }

  public function getContent()
  {
    $currentPosition = ftell($this->fp);
    rewind($this->fp);
    $csv = stream_get_contents($this->fp);
    fseek($this->fp, $currentPosition);

    return $csv;
  }

  public function saveToFile($fileName)
  {
    throw new Exception(__METHOD__.':'.__LINE__.'|Not implemented');
  }

  public static function csvToString($data, $colHeaders = array())
  {
    $mem = 5*1024*1024;
    $stream = fopen ('php://temp/maxmemory:'.$mem, 'r+');

    if (!empty($colHeaders))
    {
      fputcsv($stream, $colHeaders);
    }

    foreach ($data as $record)
    {
      fputcsv($stream, $record);
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    return $csv;
  }
}