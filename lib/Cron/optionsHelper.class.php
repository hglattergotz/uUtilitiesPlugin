<?php
/**
 * OptionsHelper
 *
 * @author Henning Glatter-Götz <henning@glatter-gotz.com>
 */
class OptionsHelper
{
  /**
   * makeOptionsString
   *
   * Turn the options passed to the command (task) back into the command line
   * string that was issued on the command line.
   *
   * @param array $options       The key/value pairs that represent the option
   *                             name and its value. Essentially these are the
   *                             options that were passed on the command line
   *                             and the values for them.
   * @param array $optionsConfig An array of sfCommandOption objects. This
   *                             represents the configured options for the task.
   *                             This is needed to discover the type mode of the
   *                             option in order to determine if it should be
   *                             included on the command line or not.
   * @static
   * @access public
   * @return string
   */
  public static function makeOptionsString($options, $optionsConfig)
  {
    $optionStrings = array();
    $optionsConfig = self::indexOptionsConfig($optionsConfig);

    foreach ($options as $opKey => $opVal)
    {
      if (!array_key_exists($opKey, $optionsConfig))
      {
        continue;
      }

      $str = '--'.$opKey;

      // Option value is optional or required (not of mode PARAMETER_NONE)
      if ($optionsConfig[$opKey]->acceptParameter())
      {
        if ($optionsConfig[$opKey]->isParameterOptional())
        {
          // Option value is optional and set
          if ($opVal != null)
          {
            $str .= '='.$opVal;
          }
          else
          {
            $str = '';
          }
        }
        else
        {
          // option value is required
          $str .= '='.$opVal;
        }
      }
      else
      {
        // The option does not accept a value and was not set
        if (false === $opVal)
        {
          $str = '';
        }
      }

      if ($str != '')
      {
        $optionStrings[] = $str;
      }
    }

    $optionString = implode(' ', $optionStrings);

    if ($optionString != '')
    {
      $optionString = ' '.$optionString;
    }

    return $optionString;
  }

  /**
   * indexOptionsConfig
   *
   * Turn the array of sfCommandOption objects into an associative array where
   * the key is the name of the parameter.
   *
   * @param array $optionsConfig Array of sfCommandOption objects
   * @static
   * @access private
   * @return array
   */
  private static function indexOptionsConfig($optionsConfig)
  {
    $cfg = array();

    foreach ($optionsConfig as $opt)
    {
      $cfg[$opt->getName()] = $opt;
    }

    return $cfg;
  }
}
