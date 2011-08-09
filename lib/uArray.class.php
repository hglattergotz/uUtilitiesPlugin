<?php
/**
 * @package     uUtilitiesPlugin
 * @subpackage  uArray
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uArray
{
  public static function assocArrayToCsv(array $data, $headers = array())
  {
    $mem = 5*1024*1024;
    $stream = fopen ('php://temp/maxmemory:'.$mem, 'r+');

    if (empty($headers))
    {
      $headers = (empty($data)) ? array() : array_keys($data[0]);
    }

    fputcsv($stream, $headers);

    foreach ($data as $record)
    {
      fputcsv($stream, $record);
    }

    rewind($stream);
    $csv = stream_get_contents($stream);
    fclose($stream);

    return $csv;
  }

  public static function assocArrayToXML(array $data, $root = 'records')
  {
    $xml = new SimpleXMLElement('<'.$root.'/>');

    foreach ($data as $row)
    {
      $xElem = $xml->addChild('record');
      $row = array_flip($row);
      array_walk($row, array ($xElem, 'addChild'));
    }

    return $xml->asXML();
  }

  public static function assocArrayToPrintableString($data, $separator = '|')
  {
    if (!is_array($data))
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|The data passed is not an array!');
    }

    $s = '';

    foreach ($data as $key => $value)
    {
      $s .= $key.'='.$value.$separator;
    }

    return substr($s, 0, -1);
  }

  public static function arrayCompare($expected, $actual, $order = true, $throwException = true)
  {
    try
    {
      if ($order)
      {
        if ($expected != $actual)
        {
          throw new Exception(__METHOD__.':'.__LINE__.'|Arrays do not match. '.
            'Expected: '.self::arrayValuesToPrintableString($expected).
            ' Actual: '.self::arrayValuesToPrintableString($actual));
        }
      }
      else
      {
        throw new Exception(__METHOD__.':'.__LINE__.'|Not implemented!');
      }

      return true;
    }
    catch (Exception $e)
    {
      if ($throwException)
      {
        throw $e;
      }
      else
      {
        return false;
      }
    }
  }

  private static function arrayValuesToPrintableString($data, $separator = '|')
  {
    $vals = array_values($data);
    
    return implode($separator, $vals);
  }
}