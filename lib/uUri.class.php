<?php
/**
 * A helper class that performs various tasks on URI strings.
 *
 * @package     uUtilitiesPlugin
 * @subpackage  uUri
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uUri
{
  protected $uri;

  public function  __construct($uri)
  {
    $this->uri = htmlspecialchars_decode($uri);
  }

  /**
   * Return the value of a parameter in the query portion of the uri string.
   * If it does not exist returns false.
   *
   * @param string $parameterName Name of the parameter of interest
   * @throws invalidUriException  If the URI is malformed
   * @return mixed                Parameter value if found, false otherwise
   */
  public function getQueryParameter($parameterName)
  {
    $vars = $this->parseURL($this->uri);

    if ($vars === false)
    {
      return false;
    }

    if ($this->countStringOccurances(array("\?$parameterName=", "&$parameterName="), false) > 1)
    {
      throw new invalidUriException(__METHOD__.':'.__LINE__.
              '|The uri is valid, but has more than one occurance of the variable '.
              $parameterName.':'.$this->uri);
    }

    return $this->arrayKeyExistsI($parameterName, $vars);
  }

  /**
   * In cases where the uri might be invalid it can be helpful to find out how
   * many occurances of a particual string are present.
   * This is simply a preg_match_all count of the number of times a particular
   * string is present.
   *
   * @param string $searchString   The string to look for
   * @param boolean $caseSensitive Perform case sensitive search if true
   * @return int                   The count of how many times the string is
   *                               present
   */
  public function countStringOccurances($searchString, $caseSensitive = true)
  {
    if (is_array($searchString))
    {
      $pattern = "/(".implode('|', $searchString).")/";
    }
    else
    {
      $pattern = "/$searchString/";
    }

    if (!$caseSensitive)
    {
      $pattern .= 'i';
    }

    $matches = array();

    return preg_match_all($pattern, $this->uri, $matches);
  }

  /**
   * If an invalid uri string contains multiple variables with the same name,
   * this method will extract them all and return the value if the values of ALL
   * variables are identical. If not all of them match it throws an exception.
   * As an double check the parameter expectedCount is used to ensure
   * that the number of variables found matches this count.
   *
   * @param string  $parameterName  Name of the parameter to extract
   * @param integer $expectedCount  The expected occurrances of the parameter
   * @param boolean $caseSensitive  Perform search case sensitive or not
   * @param boolean $throwException Throw and exception in case of error or not
   * @throws Exception
   * @return string
   */
  public function getQueryParamterMultiple($parameterName,
                                           $expectedCount,
                                           $caseSensitive = true,
                                           $throwException = true)
  {
    $pattern = "/^$parameterName=/";
    $pattern .= ($caseSensitive) ? '' : 'i';

    $parts = preg_split("/[?&]/", $this->uri);
    $values = array();

    foreach ($parts as $part)
    {
      if (preg_match($pattern, $part) == 1)
      {
        $chunks = explode('=', $part);
        $values[] = $chunks[1];
      }
    }

    if (count($values) != $expectedCount)
    {
      if ($throwException)
      {
        throw new Exception(__METHOD__.':'.__LINE__.
                '|Failed to extract all expected parameters with name '.
                $parameterName.'. Expected: '.$expectedCount.
                ', Found: '.count($values));
      }
      else
      {
        return false;
      }
    }
    else
    {
      $match = $values[0];

      foreach ($values as $value)
      {
        if ($match !== $value)
        {
          if ($throwException)
          {
            throw new Exception(__METHOD__.':'.__LINE__.
                    '|Not all values are the same. Found at least two values that do not match: '.
                    $match.'/'.$value);
          }
          else
          {
            return false;
          }
        }
      }

      return $match;
    }
  }

  /**
   * Test the uri for validity. Uses the parse_url php function to test if it
   * fails.
   * See parseURL for details.
   *
   * @return boolean True if it is valid, otherwise false
   */
  public function isValid()
  {
    try
    {
      $this->parseURL($this->uri);

      return true;
    }
    catch (invalidUriException $iue)
    {
      return false;
    }
  }

  /**
   * Case insensitive version of array_key_exists that returns the value if the
   * key is found.
   *
   * @param string $key  The key to search for
   * @param array $array Associative array containing the key/value pairs
   * @return mixed       Booelan false if not found otherwise the value of the
   *                     key
   */
  protected function arrayKeyExistsI($key, $array)
  {
    if (array_key_exists($key, $array))
    {
      return $array[$key];
    }

    if (!(is_string($key) && is_array($array) && count($array)))
    {
      return false;
    }

    $key = strtolower($key);

    foreach ($array as $k => $v)
    {
      if (strtolower($k) == $key)
      {
        return $v;
      }
    }

    return false;
  }

  /**
   * Parse a uri string and return the query portion of the string as an
   * associative array.
   *
   * @param string $urlString The URL string to parse
   * @throw invalidUriException
   * @return mixed Boolean false on failure otherwise an associative array
   *               containing the key/value pars or the url
   */
  protected function parseURL($urlString)
  {
    try
    {
      set_error_handler(array('uUri', 'errorHandler'));
      $query = parse_url(urldecode($urlString), PHP_URL_QUERY);
      restore_error_handler();
    }
    catch (Exception $e)
    {
      restore_error_handler();
      throw new invalidUriException(__METHOD__.':'.__LINE__.'|INVALID URI EXCEPTION|'.$e->getMessage());
    }

    if ($query === false)
    {
      return false;
    }

    parse_str($query, $variables);

    if (empty($variables))
    {
      return false;
    }

    return $variables;
  }

  /**
   * Custom error handler to use when running parse_url(). This php function can
   * emit an E_WARNING and this handler will intercept this. This method is used
   * exclusively by parseURL.
   *
   * All parameters are passed by the php error system.
   *
   * @param integer $errno
   * @param string $errstr
   * @param string $errfile
   * @param integer $errline
   * @throws invalidUriException
   * @return boolean
   */
  protected static function errorHandler($errno, $errstr, $errfile, $errline)
  {
    switch ($errno)
    {
    case E_WARNING:
      throw new invalidUriException(__METHOD__.':'.__LINE__.'|PHP WARNING|NR:'.$errno.'|'.$errstr);
      break;
    }

    return true;
  }
}
