<?php
/**
 * Get the latest revision of either the local working copy or svn URL
 * 
 * @package     uUtilitiesPlugin
 * @subpackage  task
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uUtilGetsvnrevisionTask extends sfBaseTask
{
  protected function configure()
  {
     // add your own arguments here
     $this->addArguments(array(
       new sfCommandArgument('path', sfCommandArgument::REQUIRED, 'Path to either the working copy or the repository'),
     ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'doctrine'),
      // add your own options here
    ));

    $this->namespace        = 'uUtil';
    $this->name             = 'get-svn-revision';
    $this->briefDescription = 'Get the latest revision of the WC or svn URL';
    $this->detailedDescription = <<<EOF
The [uUtil:get-svn-revision|INFO] returns the latest revision of either the
working copy (WC) or an svn URL.

To get the revision of the working copy

  [php symfony uUtil:get-svn-revision|INFO] .

To get the revision of the repository trunk

  [php symfony uUtil:get-svn-revision|INFO] https://urltorepo/trunk
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $path = $arguments['path'];
    $cmd = "svn info -r 'HEAD' ".$path." | grep Revision: | cut -c11-";
    
    exec($cmd, $output, $exitCode);

    if ($exitCode === 0)
    {
      echo $output[0];
    }

    return $exitCode;
  }
}
