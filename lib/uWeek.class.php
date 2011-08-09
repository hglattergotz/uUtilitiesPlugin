<?php
/**
 * @package     uUtilitiesPlugin
 * @subpackage  uWeek
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uWeek
{
  private static function TimeStampsForWeek($year, $week, $day = 0)
  {
    $week = sprintf("%02d", $week);

    for ($i = 1; $i <= 7; ++$i)
    {
      $days[$i] = strtotime($year.'W'.$week.$i);
    }
    
    if (0 != $day)
    {
      return $days[$day];
    }
    else
    {
      return $days;
    }
  }

  public static function TimeStampsForLastWeek($day = 0)
  {
    $week = date("W");
    $year = date("Y");
    
    $lastweek = $week - 1;
    
    if ($lastweek == 0)
    {
        $lastweek = 52;
        $year--;
    }
    
    return self::TimeStampsForWeek($year, $lastweek, $day);
  }

  public static function TimeStampsForThisWeek($day = 0)
  {
    $week = date("W");
    $year = date("Y");
    
    return self::TimeStampsForWeek($year, $week, $day);
  }
}