<?php
/**
 * Basic parameter holder that can be configured to allow adding of new
 * parameters at runtime. Alternatively a class could be derived from this one
 * to set all parameters a design time. As an alternative the sfParameterHolder
 * could be used. It works in a smililar way without magic methods for the
 * getters and setters and also allows a default value to be passed to the get
 * method.
 *
 * @package     uUtilitiesPlugin
 * @subpackage  uParameterHolder
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uParameterHolder
{
  /**
   * @var array The set of parameters for this class
   */
  protected $parameters = array();

  /**
   * @var boolean If set to true the class allows adding new parameters at
   *              runtime
   */
  protected $allowNewParameters;

  /**
   * Constructor for a ParameterHolder
   *
   * @param array   $parameters         The list of parameters to use if any
   * @param boolean $allowNewParameters See member description
   */
  public function __construct($parameters = array(), $allowNewParameters = true)
  {
    foreach ($parameters as $param)
    {
      $this->parameters[$param] = '';
    }

    $this->allowNewParameters = $allowNewParameters;
  }

  /**
   * Magic setter method that allows access to private members by simply using
   * the name.
   *
   * Example:
   *   If the class has a parameter called 'foo' then it can be accessed by
   *   using ->foo
   *
   * @param string $name The name of the parameter
   * @param mixed $value Any valid PHP value
   */
  public function __set($name, $value)
  {
    if (!$this->allowNewParameters)
    {
      if (!array_key_exists($name, $this->parameters))
      {
        throw new Exception(__METHOD__.':'.__LINE__.'|The parameter '.$name.' does not exist.');
      }
    }

    $this->parameters[$name] = $value;
  }

  /**
   * Magic getter method that allows access to private members by simply using
   * the name.
   *
   * @param string $name The name of the paramter
   * @return mixed       The value of the parameter
   */
  public function __get($name)
  {
    if (isset($this->parameters[$name]))
    {
      return $this->parameters[$name];
    }
    else
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|The parameter "'.$name.
        '" does not exist.');
    }
  }

  /**
   * Check if a named paramter exists in the parameter holder object.
   * 
   * @param string $name Name of the parameter to check for
   * @return boolean 
   */
  public function has($name)
  {
    return isset($this->parameters[$name]);
  }

  /**
   * Helper method to write the contents of the parameter holder to a string.
   * This can be useful for logging purposes.
   *
   * @return String A serialized, human readable version of the class.
   */
  public function __toString()
  {
    return $this->name.'|'.uArray::assocArrayToPrintableString($this->parameters);
  }
}