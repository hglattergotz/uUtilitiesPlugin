<?php
/**
 * @package     uUtilitiesPlugin
 * @subpackage  uCron
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uCron
{
  /**
   * Creates a shell script (.sh) in $scriptFullPath that calls a symfony task
   * $sfTaskCall. It then creates a symlink to the shell scrip from $cronPath.
   *
   * $cronPath must be one of the following:
   *   - /etc/cron.hourly
   *   - /etc/cron.daily
   *   - /etc/cron.weekly
   *   - /etc/cron.monthly
   *
   * This of course requires that the system is setup for this type of
   * configuration. The method is called generic because it makes use of one of
   * these pre-defined time slots.
   *
   * $scripFullPath must be a valid path including the name of the script file.
   * It is recommended that this be in the application directory called scripts.
   *
   * Example: /var/www/httpdocs/backend/1.0.2/apps/cron/scripts/myTaskCall.sh
   *
   * $sfTaskCall must be the complete command line to call the symfony task
   * including the full path and all parameters and options.
   *
   * Example: /var/www/httpdocs/backend/1.0.2/symfony ns:task --option=something
   * 
   * @param string $cronPath       Path to cron script call (ex: /etc/cron.hourly)
   * @param strint $scriptFullPath The full path of the script that cron will call
   * @param string $sfTaskCall     The sf task that the script should call (including parameters)
   */
  public static function generic($scriptFullPath, $sfTaskCall, $cronPath)
  {
    $DS = DIRECTORY_SEPARATOR;

    $pi = pathinfo($scriptFullPath);
    $scriptPath = $pi['dirname'];
    uFs::mkdir($scriptPath, true);
    uFs::chmod($scriptPath, 0755, true);

    $sh = '#!/bin/bash'.PHP_EOL;
    $sh .= 'php '.sfConfig::get('sf_root_dir').$DS.$sfTaskCall.PHP_EOL;

    $linkName = $cronPath.$DS.$pi['filename'];
    $target = $scriptFullPath;
    uFs::file_put_contents($scriptFullPath, $sh);
    uFs::chmod($scriptFullPath, 0777, true);
    uFs::symlink($target, $linkName, true);
  }

  /**
   * Install a cron job that runs at a custom time (other than the .hourly/
   * .daily/.weekly/.monthly. A file in the same format as /etc/crontab is
   * created in /etc/cron.d with permissions 644.
   * 
   * $fileName should not have any extension and is the name of the file that
   *           wil be created in $cronPath.
   *
   * $time is a string that represents the customary cron time format:
   *   minute   hour   day   month   dayofweek
   *
   *   minute    - any integer from 0 to 59
   *   hour      - any integer from 0 to 23
   *   day       - any integer from 1 to 31 (must be a valid day if a month is
   *               specified)
   *   month     - any integer from 1 to 12 (or the short name of the month such
   *               as jan or feb)
   *   dayofweek - any integer from 0 to 7, where 0 or 7 represents Sunday (or
   *               the short name of the week such as sun or mon)
   *
   *   Example: run the command at 7 am every day
   *            0 7 * * *
   *
   * $sfTaskCall is the symfony command line that the script should execute.
   *
   * @param <type> $cronPath   Path to the cron directory (must be /etc/cron.d)
   * @param <type> $fileName   Name of the file to be created in $cronPath
   * @param <type> $time       Cron time format (see above)
   * @param <type> $sfTaskCall The command to call
   */
  static public function custom($fileName, $time, $sfTaskCall, $cronPath = '/etc/cron.d')
  {
    $DS = DIRECTORY_SEPARATOR;
    $fullPath = $cronPath.$DS.$fileName;
    $command = $time.' root php '.sfConfig::get('sf_root_dir').$DS.$sfTaskCall.PHP_EOL;
    uFs::file_put_contents($fullPath, $command);
    uFs::chmod($fullPath, 0644, true);
  }

  /**
   * Determine if a cron scheduling string is equivalent to the dateTime string
   * passed as the second paramter.
   *
   * Example:
   *   $cron = '* * * * *';
   *   $dateString = '2010-08-10 22:02:00';
   *
   *   In this case the function would return true since the cron string
   *   indicates that the job should execute every minute.
   *
   *   $cron = '* 15 * * *';
   *   $dateString = '2010-08-10 22:02:00';
   *
   *   This would result in false, since the hour in the cron string is 15 and
   *   the hour in the dateTime string is 22. Therefor no match.
   *
   * @param string $cron
   * @param string $dateString
   * @return boolean
   */
  public static function matchCronToTimeStamp($cron, $dateString)
  {
    $cr = self::cronStringToArray($cron);
    $str = self::timeStampToArray($dateString);

    if ($cr['minute'] != $str['minute'] && $cr['minute'] != '*')
    {
      return false;
    }

    if ($cr['hour'] != $str['hour'] && $cr['hour'] != '*')
    {
      return false;
    }

    if ($cr['day'] != $str['day'] && $cr['day'] != '*')
    {
      return false;
    }

    if ($cr['month'] != $str['month'] && $cr['month'] != '*')
    {
      return false;
    }

    if ($cr['day_of_week'] != $str['day_of_week'] && $cr['day_of_week'] != '*')
    {
      return false;
    }

    return true;
  }

  /**
   * Return an array that contains the parts of a cron configuration string.
   *
   * @return array
   */
  protected static function getCronPartsArray()
  {
    return array('minute' => '', 'hour' => '', 'day' => '', 'month' => '', 'day_of_week' => '');
  }

  /**
   * Convert a cron configuration string into an array.
   *
   * @param string $cron
   * @return array
   */
  protected static function cronStringToArray($cron)
  {
    $parts = self::getCronPartsArray();
    list($parts['minute'], $parts['hour'], $parts['day'], $parts['month'], $parts['day_of_week']) = explode(' ', $cron);

    if ($parts['day_of_week' == '0'])
    {
      $parts['day_of_week'] = '7';
    }

    return $parts;
  }

  /**
   * Convert a date time string into the components of a cron configuration
   * string.
   *
   * @param string $dateString
   * @return array
   */
  protected static function timeStampToArray($dateString)
  {
    $parts = self::getCronPartsArray();
    $timeStamp = uDateTime::strtotime($dateString);
    $parts['minute'] = intval(date("i", $timeStamp));
    $parts['hour'] = date("G", $timeStamp);
    $parts['day'] = date("j", $timeStamp);
    $parts['month'] = date("n", $timeStamp);
    $parts['day_of_week'] = date("N", $timeStamp);

    return $parts;
  }
}