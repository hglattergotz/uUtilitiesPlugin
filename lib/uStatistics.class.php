<?php
/**
 * Statistics container that allows for arbitrary variables to be collected and
 * printed out. The constructor will optionally start a timer that reports on
 * the elapsed time between construction and the call to the __toString method.
 *
 * @package     uUtilitiesPlugin
 * @subpackage  uStatistics
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uStatistics
{
  protected $name;
  protected $values = array();
  protected $timer;

  public function __construct($name = '', $recordTime = false)
  {
    $this->name = $name;

    if ($recordTime)
    {
      $this->timer = new sfTimer();
      $this->timer->startTimer();
    }
    else
    {
      $this->timer = null;
    }
  }

  /**
   * Set the name of the statistics object. This will be printed out in the
   * __toString method.
   *
   * @param string $val Display name of the object
   */
  public function setName($val)
  {
    $this->name = $val;
  }

  /**
   * initMembers
   *
   * A quicker way to add stats items (values to track) that are initialized to
   * a the value passed to the method.
   *
   * @param array $members
   * @param int $initValue
   * @access public
   * @return void
   */
  public function initMembers(array $members, $initValue = 0)
  {
    foreach ($members as $member)
    {
      $this->values[$name] = $initValue;
    }
  }

  /**
   * Magic method to set a variable of an arbitrary name. This allows something
   * like the following:
   *
   * $stats->myVar = 'blabla';
   *
   * The above will create a property of name myVar with a value of 'blabla'.
   *
   * @param string $name
   * @param string $value
   */
  public function __set($name, $value)
  {
    $this->values[$name] = $value;
  }

  /**
   * Getter that returns the value of a property if it exists.
   *
   * @param string $name
   * @return mixed
   * @throws Exception Throws if the property does not exist
   */
  public function __get($name)
  {
    if (isset($this->values[$name]))
    {
      return $this->values[$name];
    }
    else
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|The property '.$name.
        ' does not exist.');
    }
  }

  /**
   * Allow for the object to be "printed" out in a human readable form. This is
   * useful for logging.
   *
   * @return string
   */
  public function __toString()
  {
    if ($this->timer !== null)
    {
      $this->values['elapsedTime'] = $this->timer->getElapsedTime();
    }

    return $this->name.'|'.uArray::assocArrayToPrintableString($this->values);
  }

  /**
   * Return the results as an array.
   *
   * @return array
   */
  public function toArray()
  {
    if ($this->timer !== null)
    {
      $this->values['elapsedTime'] = $this->timer->getElapsedTime();
    }

    $this->values['StatsDescription'] = $this->name;

    return $this->values;
  }
}
