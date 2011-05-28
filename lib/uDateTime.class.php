<?php
/**
 * Utility class that wraps frequently used datetime functions and makes them
 * application specific (eg. throw exceptions).
 *
 * @package     uSettingsDoctrinePlugin
 * @subpackage  uDateTime
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uDateTime
{
  /**
   * Protected helper method that takes care of time zone management and
   * internally calls the date() function to generate a date string in the
   * desired timezone.
   * 
   * @param string $timeZone A valid TZ identifier
   *                         (http://us.php.net/manual/en/timezones.php).
   *                         If null, then the default is used (whatever is set)
   * @param integer $hour    Hour portion of the date
   * @param integer $minute  Minute portion of the date
   * @param integer $second  Second  portion of the date
   * @param integer $month   Month portion of the date
   * @param integer $day     Day portion of the date
   * @param integer $year    Year portion of the date
   *
   * @return string          YYYY-MM-DD hh:mm:ss formatted string
   * @throw  Exception       If the passed timezone identifier is not valid
   */
  protected static function makeDateTime($timeZone, $hour = 0, $minute = 0, $second = 0, $month = null, $day = null, $year = null)
  {
    if (null !== $timeZone)
    {
      $currentTZ = date_default_timezone_get();
    
      if (!date_default_timezone_set($timeZone))
      {
        throw new Exception(__METHOD__.':'.__LINE__.'|'.
                            $timeZone.' is not a valid time zone identifier!');
      }
    }
    
    if ($month == null && $day == null && $year == null)
    {
      $date = date("Y-m-d H:i:s");
    }
    else
    {
      $date = date("Y-m-d H:i:s", mktime($hour, $minute, $second, $month, $day, $year));
    }
    
    if (null !== $timeZone)
    {
      date_default_timezone_set($currentTZ);
    }
    
    return $date;
  }

  /**
   * Wrapper for strtotime that throws an exception.
   *
   * @param string $val The string representing a date time
   * @return int        A unix time stamp
   */
  public static function strtotime($val)
  {
    $ts = strtotime($val);

    if ($ts === false)
    {
      throw new Exception(__METHOD__.':'.__LINE__.'|Invalid date time string: '.
        $val.', cannot convert to timestamp');
    }

    return $ts;
  }

  /**
   * Get the datetime (YYYY-MM-DD hh:mm:ss) string for NOW based on the passed
   * timezone
   * 
   * @param string $timeZone The timezone identifier
   * 
   * @return string          YYYY-MM-DD hh:mm:ss formatted string
   * @throw  Exception       If the passed timezone identifier is not valid
   */
  public static function now($timeZone = null)
  {
    return self::makeDateTime($timeZone);
  }
  
  /**
   * Return the datetime (YYYY-MM-DD hh:mm:ss) string for the beginning of this
   * month based on the timezone
   * 
   * @param string $timeZone The timezone identifier
   *
   * @return string          YYYY-MM-DD hh:mm:ss formatted string
   * @throw  Exception       If the passed timezone identifier is not valid
   */
  public static function beginningOfThisMonth($timeZone = null)
  {
    return self::makeDateTime($timeZone, 0, 0, 0, date("m"), 1, date("Y"));
  }
  
  public static function beginningOfLastMonth($timeZone = null)
  {
    return self::makeDateTime($timeZone, 0, 0, 0, date("m")-1, 1, date("Y"));
  }
  
  /**
   * Return the datetime (YYYY-MM-DD hh:mm:ss) string for the end of last month
   * based on the timezone
   * 
   * @param string $timeZone The timezone identifier
   *
   * @return string          YYYY-MM-DD hh:mm:ss formatted string
   * @throw  Exception       If the passed timezone identifier is not valid
   */
  public static function endOfLastMonth($timeZone = null)
  {
    return self::makeDateTime($timeZone, 23, 59, 59, date("m"), 0, date("Y"));
  }
  
  /**
   * Return the datetime (YYYY-MM-DD hh:mm:ss) string for the end of yesterday
   * based on the timezone
   * 
   * @param string $timeZone The timezone identifier
   *
   * @return string          YYYY-MM-DD hh:mm:ss formatted string
   * @throw  Exception       If the passed timezone identifier is not valid
   */
  public static function endOfYesterday($timeZone = null)
  {
    return self::makeDateTime($timeZone, 23, 59, 59, date("m"), date("d")-1, date("Y"));
  }

  public static function beginningOfThisWeek($timeZone = null)
  {
    $ts = uWeek::TimeStampsForThisWeek(1);
    
    return self::makeDateTime($timeZone, 0, 0, 0, date("m", $ts), date("d", $ts), date("Y", $ts));
  }
  
  public static function beginningOfLastWeek($timeZone = null)
  {
    $ts = uWeek::TimeStampsForLastWeek(1);

    return self::makeDateTime($timeZone, 0, 0, 0, date("m", $ts), date("d", $ts), date("Y", $ts));
  }
  
  public static function endOfLastWeek($timeZone = null)
  {
    $ts = uWeek::TimeStampsForLastWeek(7);
    
    return self::makeDateTime($timeZone, 23, 59, 59, date("m", $ts), date("d", $ts), date("Y", $ts));
  }
  
  public static function rangeDayBeforeYesterday($timeZone = null)
  {
    $r = array('startDate' => self::makeDateTime($timeZone, 0, 0, 0, date("m"), date("d")-2, date("Y")),
               'endDate'   => self::makeDateTime($timeZone, 23, 59, 59, date("m"), date("d")-2, date("Y")));
    
    return $r;
  }
  
  public static function rangeYesterday($timeZone = null)
  {
    $r = array('startDate' => self::makeDateTime($timeZone, 0, 0, 0, date("m"), date("d")-1, date("Y")),
               'endDate'   => self::makeDateTime($timeZone, 23, 59, 59, date("m"), date("d")-1, date("Y")));
    
    return $r;
  }
  
  public static function rangeLastWeek($timeZone = null)
  {
    $r = array('startDate' => self::beginningOfLastWeek($timeZone),
               'endDate'   => self::endOfLastWeek($timeZone));
    
    return $r;
  }

  public static function rangeLastMonth($timeZone = null)
  {
    $r = array('startDate' => self::beginningOfLastMonth($timeZone),
               'endDate'   => self::endOfLastMonth($timeZone));
    
    return $r;
  }
  
  public static function daysBetweenDates($startDate, $endDate)
  {
    return uDate::daysBetweenDates(substr($startDate, 0, 10), substr($endDate, 0, 10));
  }

  public static function nDaysAgo($days, $timeZone = null)
  {
    $r = self::makeDateTime($timeZone, 0, 0, 0, date("m"), date("d")-$days, date("Y"));

    return $r;
  }

  /**
   * The first day of the month into which the passed date falls.
   * 
   * @param string $date     String representation of a date
   * @param string $timeZone The timezone if any
   * @return string          The datetime of the first day of the month
   */
  public static function firstDayOfMonth($date, $timeZone = null)
  {
    $month = date("n", self::strtotimeTZ($date, $timeZone));
    $year = date("Y", self::strtotimeTZ($date, $timeZone));
    $r = self::makeDateTime($timeZone, 0, 0, 0, $month, 1, $year);

    return $r;
  }

  /**
   * Return the last day of the month for the date passed. The data can be any
   * date, the method will return the last day of that month and year.
   *
   * @param string $date     String representation of a date
   * @param string $timeZone The timezone if any
   * @return string          The datetime of the last day of the month
   */
  public static function lastDayOfMonth($date, $timeZone = null)
  {
    $ts = self::strtotimeTZ($date, $timeZone);
    $month = date("n", $ts);
    $year = date("Y", $ts);
    $daysInMonth = date("t", $ts);
    $r = self::makeDateTime($timeZone, 0, 0, 0, $month, $daysInMonth, $year);

    return $r;
  }

  /**
   * Convert a date string to a timestamp based on the timezone
   *
   * @param string $dateStr  The string to convert
   * @param string $timeZone The timezone identifier
   *
   * @return integer         A UNIX timestamp
   * @throw  Exception       If the passed timezone identifier is not valid
   */
  public static function strtotimeTZ($dateStr, $timeZone = null)
  {
    if (null === $timeZone)
    {
      $timeStamp = self::strtotime($dateStr);
    }
    else
    {
      $currentTZ = date_default_timezone_get();

      if (!date_default_timezone_set($timeZone))
      {
        throw new Exception(__METHOD__.':'.__LINE__.'|'.
                            $timeZone.' is not a valid time zone identifier!');
      }

      $timeStamp = self::strtotime($dateStr);

      date_default_timezone_set($currentTZ);
    }

    return $timeStamp;
  }

  /**
   * Convert an English date time string to an application date time string.
   * The application date time format is YYYY-MM-DD HH:MM:SS
   *
   * @param string $dateTimeStr The string that holds the date time
   * @return string             The app formatted date time string
   * @throw Exception
   */
  public static function strToAppDateTime($dateTimeStr)
  {
    $ts = self::strtotime($dateTimeStr);

    return date("Y-m-d H:i:s", $ts);
  }

  public static function elapsedSeconds($startDate, $endDate)
  {
    $sTs = self::strtotime($startDate);
    $eTs = self::strtotime($endDate);
    
    return abs($sTs-$eTs);
  }
  
  public static function format($seconds, $accuracy = 2)
  {
    $seconds = intval($seconds);
    $d = intval($seconds / (3600 * 24));
    $h = bcmod($seconds / 3600, 24);
    $m = bcmod($seconds / 60, 60);
    $s = bcmod($seconds,60);
    
    $f = '';
    $count = 0;
    
    if ($d > 0)
    {
      $f .= $d;
      $f .= ($d == 1) ? ' day ' : ' days ';
      ++$count;
    }
    
    if ($d > 0 || $h > 0)
    {
      $f .= $h;
      $f .= ($h == 1) ? ' hour ' : ' hours ';
      ++$count;
    }
    
    if ($d > 0 || $h > 0 || $m > 0)
    {
      if ($count < $accuracy)
      {
        $f .= $m;
        $f .= ($m == 1) ? ' minute ' : ' minutes ';
        ++$count;
      }
    }
    
    if ($count < $accuracy)
    {
      $f .= $s;
      $f .= ($s == 1) ? ' second' : ' seconds';
    }
    
    return $f;
  }
}
