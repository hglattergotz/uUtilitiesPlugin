<?php
/**
 * MySQL backup task.
 * 
 * @package     uUtilitiesPlugin
 * @subpackage  task
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uBackupTask extends sfBaseTask
{
  protected $taskSchedulerDescription = 'Project database backup utility';

  protected function configure()
  {
    // add your own arguments here
    $this->addArguments(array(
      new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'The path of the backup'),
      new sfCommandArgument('stemname', sfCommandArgument::REQUIRED, 'The backup file stem name'),
    ));

    $this->addOptions(array(
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment name', 'prod'),
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      new sfCommandOption('compress', null, sfCommandOption::PARAMETER_NONE, 'Compress the dump output using gzip'),
      new sfCommandOption('overwrite', null, sfCommandOption::PARAMETER_NONE, 'Overwrite an existing backup with the same name'),
      new sfCommandOption('keepnfiles', null, sfCommandOption::PARAMETER_REQUIRED, 'The number of backup files to keep', -1),
      new sfCommandOption('install', null, sfCommandOption::PARAMETER_NONE, 'Install a cron job for this task'),
      new sfCommandOption('crontime', null, sfCommandOption::PARAMETER_REQUIRED, 'The cron time formatted string', '0 23 * * *'),
      new sfCommandOption('cronpath', null, sfCommandOption::PARAMETER_REQUIRED, 'The path from which cron jobs are executed', '/etc/cron.d'),
      new sfCommandOption('cronprefix', null, sfCommandOption::PARAMETER_REQUIRED, 'A string to be prepended to the cronjob file name'),
    ));

    $this->namespace        = 'uUtil';
    $this->name             = 'backup';
    $this->briefDescription = 'Backup the project database';
    $this->detailedDescription = <<<EOF
The [uUtil:backup|INFO] task backs up the project database to a specified
location and optionally manages the number of backup files it keeps in that location.

A more detailed explanation of the arguments and options follows:

ARGUMENTS

 path .......... The directory where the backup files should be stored. If this
                 does not exist it is created.
 stemname ...... The backup file stem name that should be used for all backup
                 files. The backup task will append the date to this stem name to
                 create the full file name.

OPTIONS

 env ........... The environment to run in
 application ... The application for which the configuration will be loaded
 connection .... The database connection
 compress ...... If set, compresses the dump file using gzip
 overwrite ..... Overwrite an existing backup file (for example, if two backups
                 are run on the same day the backup would try to create two files
                 with the same name)
 keepnfiles .... The number of files to keep and above which to start pruning.
 install ....... Install this task as a cron job
 crontime ...... The execution time for the cron job
 cronpath ...... The location of the cron file
 cronprefix .... A string that will be prepended to the cron job file

Call it as follows to generate a backup file in the specified location and file
stem name. The backup will NOT be compressed or overwritten.
No pruning will take place:

  [symfony uUtil:backup|INFO] PATH STEM_NAME --application=APPNAME

To install the task as a cron job using the default time and location.

  [symfony uUtil:backup|INFO] PATH STEM_NAME --install

A more likely option for installing this as a cron job would be

  [symfony uUtil:backup|INFO] PATH STEM_NAME --install --keepnfiles=7

Note that the cron installation by default will set --compress and --overwrite
because it makes sense to set these for a continuous backup situation.

EOF;
  }

  private function installCron($arg, $opt)
  {
    $scriptName = $opt['cronprefix'].'backup_project';
    $sfTaskCall = 'symfony util:backup '.$arg['path'].' '.$arg['stemname'].
      ' --env='.$opt['env'].' --application='.$opt['application'].
      ' --compress --overwrite --keepnfiles='.$opt['keepnfiles'];

    uCron::custom($scriptName, $opt['crontime'], $sfTaskCall, $opt['cronpath']);
  }

  private function backup($arg, $opt)
  {
    $result = '';
    $fullPath = uDbBackup::makeDatedFullPath($arg['path'], $arg['stemname'], $opt['connection']);
    $opt['stemname'] = $arg['stemname'];

    return uDbBackup::database($fullPath, $result, $opt);
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    if ($options['install'])
    {
      return $this->installCron($arguments, $options);
    }
    else
    {
      return $this->backup($arguments, $options);
    }
  }
}
