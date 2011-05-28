<?php
/**
 * A collection of date functions that operate on the YYYY-MM-DD format that is
 * also used in mysql.
 * Some methods are time zone sensitive, meaning that if a time zone is passed
 * the resulting string will be according to the time zone.
 *
 * Methods are mostly self explanatory based on their name and therefore not
 * documented. Methods that are not 100% clear are documented.
 *
/**
 * @package     uSettingsDoctrinePlugin
 * @subpackage  uDate
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uDate
{
  public static function today($timeZone = null)
  {
    return substr(uDateTime::now($timeZone), 0, 10);
  }
  
  public static function yesterday($timeZone = null)
  {
    return substr(uDateTime::endOfYesterday($timeZone), 0, 10);
  }
  
  public static function beginningOfThisWeek($timeZone = null)
  {
    return substr(uDateTime::beginningOfThisWeek($timeZone), 0, 10);
  }
  
  public static function beginningOfLastWeek($timeZone = null)
  {
    return substr(uDateTime::beginningOfLastWeek($timeZone), 0, 10);
  }
  
  public static function endOfLastWeek($timeZone = null)
  {
    return substr(uDateTime::endOfLastWeek($timeZone), 0, 10);
  }
  
  public static function beginningOfThisMonth($timeZone = null)
  {
    return substr(uDateTime::beginningOfThisMonth($timeZone), 0, 10);
  }
  
  public static function beginningOfLastMonth($timeZone = null)
  {
    return substr(uDateTime::beginningOfLastMonth($timeZone), 0, 10);
  }
  
  public static function endOfLastMonth($timeZone = null)
  {
    return substr(uDateTime::endOfLastMonth($timeZone), 0, 10);
  }
  
  public static function daysBetweenDates($startDate, $endDate)
  {
    return round((strtotime($endDate)-strtotime($startDate))/(60*60*24))+1;
  }

  /**
   * Return the first day of the month into which the passed date falls.
   *
   * @param string $date     Any valid date string
   * @param string $timeZone
   * @return string
   */
  public static function firstDayOfMonth($date, $timeZone = null)
  {
    return substr(uDateTime::firstDayOfMonth($date, $timeZone), 0, 10);
  }

  /**
   * Return the last day of the month into which the passed date falls.
   *
   * @param string $date     Any valid date string
   * @param string $timeZone
   * @return string
   */
  public static function lastDayOfMonth($date, $timeZone = null)
  {
    return substr(uDateTime::lastDayOfMonth($date, $timeZone), 0, 10);
  }

  /**
   * Convert a gregorian time format string to the format native to this class.
   * Gregorian is MM/DD/YYYY
   *
   * @param string $gregorian String containing a gregorian formatted date.
   * @return string
   */
  public static function fromGregorianString($gregorian)
  {
    return date('Y-m-d', strtotime($gregorian));
  }

  public static function nDaysAgo($days, $timeZone = null)
  {
    return substr(uDateTime::nDaysAgo($days, $timeZone), 0, 10);
  }

  /**
   * Convert an English date time string to an application date string.
   * The application date time format is YYYY-MM-DD HH:MM:SS
   *
   * @param string $dateTimeStr The string that holds the date time
   * @return string             The app formatted date time string
   * @throw Exception
   */
  public static function strToAppDate($dateStr)
  {
    return substr(uDateTime::strToAppDateTime($dateStr), 0, 10);
  }
}
