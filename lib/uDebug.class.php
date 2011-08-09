<?php
/**
 * Collection of debug functions that come in hand when troubleshooting issues
 * in symfony.
 * Depends on fs.
 *
 * @package     uUtilitiesPlugin
 * @subpackage  uDebug
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uDebug
{
  /**
   * Get the name of a variable as a string.
   *
   * @param mixed $var A reference to the variable for which the name is needed
   * @param boolean $scope 
   * @param <type> $prefix
   * @param <type> $suffix
   * @return <type>
   */
  public static function varName(&$var, $scope=false, $prefix='unique', $suffix='value')
  {
    $vals = ($scope) ? $scope : $GLOBALS;
    
    $old = $var;
    $var = $new = $prefix.rand().$suffix;
    $vname = FALSE;
    
    foreach ($vals as $key => $val)
    {
      if($val === $new)
      {
        $vname = $key;
      }
    }
    
    $var = $old;
    
    return $vname;
  }

  /**
   * A bit of an expanded echo function. It recognizes if the request is coming
   * from an HTTP client or is CLI and modifies the output accordingly.
   *
   * @param string $label         The label that is printed in front of the value
   * @param mixed $val            The value to be echoed
   * @param boolean $newLine      Append a newline or not
   * @param boolean $returnResult Return the result or echo it
   * @return string               If $returnResult is true
   */
  public static function echoVar($label, $val, $newLine = true, $returnResult = false)
  {
    $result = '';

    if (!self::isCLI())
    {
      $result .= '<pre>'.$label.' =<br/>'.print_r($val, true).'</pre>';
    }
    else
    {
      $result .= $label.' = '.print_r($val, true);
    }
    
    if ($newLine)
    {
      if (self::isCLI())
      {
        $result .= PHP_EOL;
      }
      else
      {
        $result .= '<br/>';
      }
    }

    if ($returnResult)
    {
      return $result;
    }
    else
    {
      echo $result;

      return;
    }
  }

  /**
   * A more extensive dump method.
   * 
   */
  public static function dump()
  {
    list($callee) = debug_backtrace();
    $arguments = func_get_args();
    $total_arguments = count($arguments);

    if (self::isCLI())
    {
      echo 'Result from dump() '.$callee['file'].' @ line: '.$callee['line'].PHP_EOL;
      $i = 0;

      foreach ($arguments as $argument)
      {
          echo (++$i).' of '.$total_arguments.PHP_EOL;
          var_dump($argument);
          echo PHP_EOL;
      }
    }
    else
    {
      echo '<fieldset style="background: #fefefe !important; border:2px red solid; padding:5px">';
      echo '<legend style="background:lightgrey; padding:5px;">'.$callee['file'].' @ line: '.$callee['line'].'</legend><pre>';
      $i = 0;

      foreach ($arguments as $argument)
      {
          echo '<br/><strong>Debug #'.(++$i).' of '.$total_arguments.'</strong>: ';
          var_dump($argument);
      }

      echo "</pre>";
      echo "</fieldset>";
    }
  }

  protected static function isCLI()
  {
    return !isset($_SERVER['HTTP_USER_AGENT']);
  }

  public static function echoNl($value)
  {
    echo $value.PHP_EOL;
  }

  /**
   * Adhoc logger that simply writes the message to a file.
   *
   * @param string $msg  The string to be logged
   * @param string $file The file name for the log file
   */
  public static function log($msg, $file = 'debug.log')
  {
    $f = uFs::fopen($file, 'a');
    fwrite($f, $msg.PHP_EOL);
    fclose($f);
  }

  /**
   * Expanded var_dump function that also returns the response if needed instead
   * of simply echoing it.
   *
   * @param mixed $var              The variable to be dumped
   * @param boolean $returnResponse Return the result or echo it
   * @return string                 The dumped var if $returnResponse is true
   */
  public static function varDump($var, $returnResponse = false)
  {
    if ($returnResponse)
    {
      ob_start();
      var_dump($var);
      $content = ob_get_contents();
      ob_end_clean();

      return $content;
    }
    else
    {
      var_dump($var);

      return;
    }
  }

  /**
   * Helper method used during development to show the usage of memory at a
   * particular point of execute.
   * Optionally a value for the last known memory usage can be passed in to
   * show the change of usage.
   *
   * @param string $label      The label to print on the screen for each memory
   *                           report
   * @param integer $lastUsage The value for the last known memory usage
   */
  protected function reportMemUsage($label = '', $lastUsage = null)
  {
    $now = round(memory_get_usage(true)/1048576,2);

    if ($lastUsage === null)
    {
      echo $label.' Memory Usage = '.$now.PHP_EOL;
    }
    else
    {
      $delta = $now - $lastUsage;
      echo $label.' Memory Usage = '.$now.' / Delta = '.$delta.PHP_EOL;
    }
  }

}