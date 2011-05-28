<?php
/* 
 * Parse a string representation of paramters into an associative array.
 *
 * Format of string:
 * parameterName=value:type,parameterName=value:type
 *
 * @package     uSettingsDoctrinePlugin
 * @subpackage  uParameterParser
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uParameterParser
{
  public static function parse($string, $throwException = true)
  {
    try
    {
      $result = array();

      if (empty($string))
      {
        return $result;
      }

      $tripples = explode(',', $string);

      foreach ($tripples as $tripple)
      {
        $double = explode('=', $tripple);

        $name = $double[0];

        if (strpos($double[1], ':') === false)
        {
          throw new Exception(__METHOD__.':'.__LINE__
                  .'|The paramter string to be parsed contains one or more paramters that do not have a type specification: '
                  .$string);
        }

        list($value, $type) = explode(':', $double[1]);

        switch ($type)
        {
          case 'string':
            $result[$name] = strval($value);
            break;
          case 'date':
            $result[$name] = uDate::strToAppDate($value);
            break;
          case 'datetime':
            $result[$name] = uDateTime::strToAppDateTime($value);
            break;
          case 'integer':
            $result[$name] = intval($value);
            break;
          default:
            throw new Exception(__METHOD__.':'.__LINE__
                    .'|The paramters string contains a type specification that is not supported: '
                    .$type);
            break;
        }
      }

      return $result;
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
}
