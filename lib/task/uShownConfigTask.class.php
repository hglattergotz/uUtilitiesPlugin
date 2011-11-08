<?php
/**
 * Emits the sfConfig array for the given application and environment. This is
 * sometimes faster than digging through the yml files.
 *  
 * @package     uUtilitiesPlugin
 * @subpackage  task
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uUtilShowconfigTask extends sfBaseTask
{
  protected function configure()
  {
    // // add your own arguments here
    // $this->addArguments(array(
    //   new sfCommandArgument('my_arg', sfCommandArgument::REQUIRED, 'My argument'),
    // ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'uUtil';
    $this->name             = 'showconfig';
    $this->briefDescription = 'Show the project configuration';
    $this->detailedDescription = <<<EOF
The [uUtil:showconfig|INFO] emits the sfConfig array for the given application
and environment.

Call it with:

  [php symfony uUtil:showconfig|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
//    $connection = $databaseManager->getDatabase($options['connection'])->getConnection();

    $cfg = sfConfig::getAll();

    if (!isset($options['application']))
    {
      echo ' NOTICE: set --application if you want the application config displayed.'.PHP_EOL;
    }

    if (!isset($options['env']))
    {
      echo ' NOTICE: set --env if you want the application config displayed.'.PHP_EOL;
    }

    echo 'Configuration for env = '.$options['env'].', application = '.$options['application'].PHP_EOL.PHP_EOL;
    print_r($cfg);
  }
}
