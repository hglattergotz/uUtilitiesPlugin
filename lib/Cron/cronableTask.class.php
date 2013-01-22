<?php
/**
 * cronableTask
 *
 * Provide functionality common to all task that should be installed as cron
 * jobs.
 *
 * NOTE: This has only been tested on Debian (Ubuntu). It does definitely NOT
 *       work on Windos.
 *
 * Usage:
 *
 *   A task should extend cronableTask instead of sfBaseTask.
 *   The following options should be configured:
 *
 *   $this->addOptions(array(
 *     new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'appname'),
 *     new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
 *     new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
 *     new sfCommandOption('install', null, sfCommandOption::PARAMETER_NONE, 'Install the cron job'),
 *     new sfCommandOption('cronpath', null, sfCommandOption::PARAMETER_REQUIRED, 'The cron path', '/etc/cron.d'),
 *     new sfCommandOption('crontime', null, sfCommandOption::PARAMETER_REQUIRED, 'The cron time', '1 * * * *'),
 *   ));
 *
 *   In addition to the above options the member scriptPrefix must be set.
 *   This will be used to create the cron file and allows for grouping cron jobs
 *   into "namespaces".
 *
 * @uses sfBaseTask
 * @author Henning Glatter-Götz <henning@glatter-gotz.com>
 */
abstract class cronableTask extends sfBaseTask
{
  /**
   * scripPrefix
   *
   * @var string
   * @access protected
   */
  protected $scripPrefix = 'PREFIX_NOT_SET';

  /**
   * installCron
   *
   * Install the task as a cron job
   *
   * @param array $options An array of key/value pairs where key is the name of
   *                       the option and the value is the value that was set on
   *                       the command line
   * @access protected
   * @return integer
   */
  protected function installCron($options)
  {
    $scriptName = $this->scriptPrefix.'_'.$this->namespace.'_'.$this->name;
    $sfTaskCall = 'symfony '.$this->namespace.':'.$this->name.optionsHelper::makeOptionsString($options, $this->options);

    uCron::custom($scriptName, $options['crontime'], $sfTaskCall, $options['cronpath']);

    return 0;
  }
}
