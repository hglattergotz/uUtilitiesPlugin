<?php
/**
 * @package     uSettingsDoctrinePlugin
 * @subpackage  uValidate
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uValidate
{
  public static function email($value, $throwException = false)
  {
    $rc = true;
    $pat = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i";
    $ev = preg_match($pat, $value);

    if (1 !== $ev)
    {
      if ($throwException)
      {
        throw new Exception('The email address \''.$value.'\' is not valid!');
      }

      $rc = false;
    }

    return $rc;
  }

  public static function dateTime($value, $throwException = false)
  {
    $rc = true;
    $ev = preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $value);

    if (1 !== $ev)
    {
      if ($throwException)
      {
        throw new Exception('Datetime string \''.$value.
          '\' does not match required format (YYYY-MM-DD HH:MM:SS)');
      }

      $rc = false;
    }

    return $rc;
  }

  public static function time($value, $throwException = false)
  {
    $rc = true;
    $ev = preg_match('/\d{2}:\d{2}:\d{2}/', $value);

    if (1 !== $ev)
    {
      if ($throwException)
      {
        throw new Exception('Time string \''.$value.
          '\' does not match required format (HH:MM:SS)');
      }

      $rc = false;
    }

    return $rc;
  }
}