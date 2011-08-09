<?php
/**
 *  Time utility class that represents HH:MM:SS and provides various methods for
 *  comparing objects of this type to each other.
 *
 * @package     uUtilitiesPlugin
 * @subpackage  uTime
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uTime
{
  protected $time;
  
  /**
   *  @param string $time The time string
   */
  public function __construct($time = 'now')
  {
    if ($time == 'now')
    {
      $time = date("H:i:s");
    }
    else
    {
      uValidate::time($time, true);
    }
    
    $this->time = date_parse($time);
  }
  
  /**
   *  Determine if $this time is greater than the passed Time.
   *
   *  @param Time $time A Time object
   */
  public function isGraterThan(uTime $time)
  {
    return $this->toSeconds() > $time->toSeconds();
  }
  
  /**
   *  Determine if $this time is less than the passed Time object.
   *
   *  @param Time $time A Time object
   */
  public function isLessThan(uTime $time)
  {
    return $this->toSeconds() < $time->toSeconds();
  }
  
  /**
   *  Determine if $this Time is between the two passed times.
   *
   *  @param Time $start
   *  @param Time @end
   */
  public function isBetween(uTime $start, uTime $end)
  {
    if ($start->isLessThan($end))
    {
      return ($this->isGraterThan($start) && $this->isLessThan($end));
    }
    else
    {
      return ($this->isGraterThan($start) || $this->isLessThan($end));
    }
  }
  
  /**
   *  function toSeconds
   *
   *  @return int The number of seconds that the time represents
   */
  private function toSeconds()
  {
    return $this->time['hour']*3600+$this->time['minute']*60+$this->time['second'];
  }
  
  public function __toString()
  {
    return date("H:i:s", mktime($this->time['hour'], $this->time['minute'], $this->time['second'], 0, 0 ,0));
  }
}
