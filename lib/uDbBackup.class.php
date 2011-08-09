<?php
/**
 * Utility class for creating backups of the entire project database or select
 * tables of the project.
 *
 * @package     uUtilitiesPlugin
 * @subpackage  uDbBackup
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uDbBackup
{
  /**
   * Build the fullpath for the backup file.
   * Checks if the target directory is writable.
   * Returns a string in the format path/stemname_YYYY-MM-DD.sql
   *
   * @param string $path         The target path where the backup file should be
   *                             written to
   * @param string $fileStemName The file stem name (the part without the date
   *                             and extension).
   * @return string              The full path including the .sql extension
   */
  public static function makeDatedFullPath($path, $fileStemName)
  {
    $extension = '.sql';
    $date = uDate::today();
    $fname = $fileStemName.'_'.$date.$extension;

    return uFs::mp(array($path, $fname));
  }

  /**
   * Prune the backup files down to keepNFiles. If $keepNFiles = -1 then don't
   * do anything
   *
   * @param <type> $path
   * @param <type> $stemName
   * @param <type> $keepNFiles
   */
  public static function pruneFiles($path, $stemName, $keepNFiles, &$output)
  {
    $outcome = true;

    if ($keepNFiles == -1)
    {
      $output = 'No files pruned because keepNFiles parameter is -1';
    }
    else
    {
      $searchPath = uFs::mp(array($path, $stemName));
      $options = array('sort-by' => 'date', 'sort-order' => 'DESC');
      $fileList = self::getListOfFiles($searchPath, $options, true);

      if (count($fileList) > $keepNFiles)
      {
        array_splice($fileList, 0, $keepNFiles);

        foreach ($fileList as $fileName)
        {
          uFs::unlink($fileName, true);
        }
      }
    }

    return $outcome;
  }

  /**
   * Backup the entire project database using the given parameters.
   * At the very least the path and the output parameters are required.
   *
   * @param string $fullPath
   * @param string $output
   * @param array $options [compact, compress, overwrite, keepnfiles, stemname]
   * @return integer
   */
  public static function database($fullPath, &$output, $options = array(), &$errors = '', &$messages = array())
  {
    $options = (!is_array($options) || empty($options)) ? self::makeDefaultOptions() : $options;
    $checkPath = ($options['compress']) ? $fullPath.'.gz' : $fullPath;
    $mysqlDumpOptions = /*($options['compact']) ? '--compact' :*/ '';
    $output = '';
    $exitCode = 1;

    try
    {
      self::checkTargetPath($fullPath, true);
    }
    catch (Exception $e)
    {
      $errors = $e->getMessage();

      return $exitCode;
    }

    if (uFs::file_exists($checkPath, false))
    {
      if ($options['overwrite'])
      {
        if (!uFs::is_writable($checkPath, false))
        {
          $errors = 'The backup file '.$checkPath.
          ' already exists and cannot be overwritten. Permission denied.';

          return $exitCode;
        }
      }
      else
      {
        $errors = 'The backup file '.$checkPath.' already exists.';

        return $exitCode;
      }
    }

    try
    {
      $cmd = self::makeFullDbDumpCmd($mysqlDumpOptions, $fullPath);

      exec($cmd, $output, $exitCode);

      if ($exitCode === 0 && $options['compress'])
      {
        $cmd = 'gzip '.$fullPath;
        exec($cmd, $output, $exitCode);
      }

      if ($exitCode === 0)
      {
        $messages[] = 'BACKUP|Successfully backed up database '.$dbName.' to '.$fullPath.'|'.self::outputToString($output);

        $pruneOutput = '';

        if (self::pruneFiles(self::getBasePath($fullPath), $options['stemname'], $options['keepnfiles'], $pruneOutput))
        {
          $messages[] = 'BACKUP PRUNE FILES|Success|'.$pruneOutput;
        }
        else
        {
          $messages[] = 'BACKUP PRUNE FILES|Failure|'.$pruneOutput;
        }
      }
      else
      {
        $errors = 'BACKUP|Failed to backup database '.$dbName.' to '.$fullPath.'|'.self::outputToString($output);
      }
    }
    catch (Exception $e)
    {
      $errors = 'BACKUP|Failed to backup database '.$dbName;
      $exitCode = 1;
    }

    return $exitCode;
  }

  /**
   *
   * @param <type> $path
   * @param <type> $options
   * @param <type> $throwException
   * @return <type>
   */
  public static function getListOfFiles($path, $options = array(), $throwException = true)
  {
    $list = array();
    $fileName = '';

    if (uFs::is_dir($path, false))
    {
      $basePath = $path;
    }
    else
    {
      $basePath = dirname($path);
      $fileName = basename($path);
    }

    uFs::is_dir($basePath, true);
    $iterator = new DirectoryIterator($basePath);

    while($iterator->valid())
    {
      $name = $iterator->getFilename();

      if (strlen($fileName) > 0)
      {
        $pattern = '/^'.$fileName.'/';

        if (1 === preg_match($pattern, $name))
        {
           $fname = $basePath.uFs::DS.$name;
           $list[] = array('file-name' => $fname, 'date' => $iterator->getCTime());
        }
      }
      else
      {
        $fname = $basePath.uFs::DS.$name;
        $list[] = array('file-name' => $fname, 'date' => $iterator->getCTime());
      }

      $iterator->next();
    }

    if (is_array($options) && !empty($options))
    {
      if (isset($options['sort-by']))
      {
        $sortBy = $options['sort-by'];

        if (isset($options['sort-order']))
        {
          $sortOrder = ($options['sort-order'] == 'ASC') ? SORT_ASC : SORT_DESC;
        }
        else
        {
          $sortOrder = SORT_ASC;
        }

        foreach ($list as $key => $row)
        {
          $sortArray[$key] = $row[$sortBy];
        }

        array_multisort($sortArray, $sortOrder, $list);
      }
    }

    $result = array();

    foreach ($list as $rec)
    {
      $result[] = $rec['file-name'];
    }

    return $result;
  }

  /**
   * Build the command line for a full database dump
   *
   * @param string $dumpOptions Additional options to be passed to mysqldump
   * @param string $fullPath    The full path of the dump file to be written
   * @return string             The command line for a mysqldump
   */
  protected static function makeFullDbDumpCmd($dumpOptions, $fullPath)
  {
    $dbName = '';
    $userName = '';
    $password = '';

    self::getDatabaseParams($dbName, $userName, $password);

    $cmd = 'mysqldump --user='.$userName.' --password='.$password;
    $cmd .= (!empty($dumpOptions)) ? ' '.$dumpOptions : '';
    $cmd .= ' '.$dbName.' 2>&1 > '.$fullPath;

    return $cmd;
  }

  /**
   * Return a set of default options for the database method
   *
   * @return <type>
   */
  protected static function makeDefaultOptions()
  {
    return array('compact' => false,
      'compress' => false,
      'overwrite' => false,
      'keepnfiles' => -1,
      'stemname' => '');
  }

  /**
   * Convert the result from the exec command from an array to a string.
   *
   * @param <type> $output
   * @return <type>
   */
  protected static function outputToString($output)
  {
    $result = '';

    foreach ($output as $line)
    {
      $result .= $line;
    }

    return $result;
  }

  /**
   * Check if the target path exists and if it is writable. Depending on the
   * $createIfNotExists parameter it will be created if it does not exist.
   *
   * @param <type> $fullPath
   * @param <type> $createIfNotExists
   * @return <type>
   */
  protected static function checkTargetPath($fullPath, $createIfNotExists = false)
  {
    $path = self::getBasePath($fullPath);

    if ($createIfNotExists)
    {
      uFs::mkdir($path, 0777, true);
    }
    else
    {
      uFs::file_exists($path, true);
      uFs::is_writable($path, true);
    }

    return true;
  }

  protected static function getBasePath($fullPath)
  {
    $pathParts = pathinfo($fullPath);

    return $pathParts['dirname'];
  }
  /**
   * Get the database parameters from the symfony environment.
   *
   * @param <type> $dbName
   * @param <type> $userName
   * @param <type> $password
   */
  protected static function getDatabaseParams(&$dbName, &$userName, &$password)
  {
    $options = Doctrine_Manager::connection()->getOptions();

    $dsn = $options['dsn'];
    $dsnComponents = explode(';', $options['dsn']);

    foreach ($dsnComponents as $component)
    {
      if (strpos($component, 'dbname') === 0)
      {
        $parts = explode('=', $component);
        $dbName = $parts[1];
        break;
      }
    }

    $userName = $options['username'];
    $password = $options['password'];
  }
}