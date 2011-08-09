<?php
/**
 * @package     uUtilitiesPlugin
 * @subpackage  uFormat
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uFormat
{
  public static function stripNonNumeric($str, $throwException = false)
  {
    $r = preg_replace("/[^\d]/",'', $str);

    if (false === $r && $throwException)
    {
      throw new Exception(__METHOD__.':'.__LINE__.
        '|An error occured in preg_replace() with string '.$str);
    }
    else
    {
      return $r;
    }
  }

  public static function stripNonNumericPreserveSpaces($str, $throwException = false)
  {
    $r = preg_replace("/[^\d\s]/",'', $str);

    if (false === $r && $throwException)
    {
      throw new Exception(__METHOD__.':'.__LINE__.
        '|An error occured in preg_replace() with string '.$str);
    }
    else
    {
      return $r;
    }
  }

  /**
   * Replace spaces with the '_' character.
   *
   * @param string $str
   * @param boolean $throwException
   * @return string
   */
  public static function spacesToUnderscore($str, $throwException = false)
  {
    $r = preg_replace("/\s+/",'_', $str);

    if (false === $r && $throwException)
    {
      throw new Exception(__METHOD__.':'.__LINE__.
        '|An error occured in preg_replace() with string '.$str);
    }
    else
    {
      return $r;
    }
  }

  /**
   * Turn a file path into a file URI
   *
   * Example: C:\windows\some\dir ==> file://localhost/C:/windows/some/dir
   *          /etc/php5           ==> file://localhost/etc/php5
   * 
   * @param string $str             The path
   * @param boolean $throwException To throw or not to throw
   */
  public static function makeFileURI($str, $throwException = false)
  {
    $r = preg_replace('/\\\/', '/', $str);
    
    if ($r[0] != '/')
    {
      $r = '/'.$r;
    }

    if (false === $r && $throwException)
    {
      throw new Exception(__METHOD__.':'.__LINE__.
        '|An error occured in preg_replace() with string '.$str);
    }
    else
    {
      return 'file://localhost'.$r;
    }
  }
}