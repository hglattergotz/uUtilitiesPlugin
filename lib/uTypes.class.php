<?php
/**
 * @package     uUtilitiesPlugin
 * @subpackage  uTypes
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uTypes
{
  /**
   * Convert a string to a float. This strips all non numeric characters except
   * the '-' and converts the string to a float.
   *
   * @param string $str The string representation of the float value
   * @return float The converted value
   */
  public static function strtofloat($str)
  {
    return floatval(ereg_replace("[^-0-9\.]", "", $str));
  }

  /**
   * Convert a string into a boolean type. PHP has some unexpected (to me at
   * least) behavior when converting strings to boolean. This is an attempt to
   * make it more intuitive.
   *
   * @param string $str The string value to be converted to a boolean type
   * @return boolean
   */
  public static function strtobool($str)
  {
    $v = strtolower($str);

    if ($v == 'yes' || $v == 'true' || $v == 'on' || $v == '1' || $v == 1)
    {
      return true;
    }
    else
    {
      return false;
    }
  }
}