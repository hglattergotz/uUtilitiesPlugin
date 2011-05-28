<?php
/**
 * @package     uSettingsDoctrinePlugin
 * @subpackage  uFs
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uFs
{
  const DS = DIRECTORY_SEPARATOR;
  const PROT_FILE = 'file://';
  const PROT_ZLIB = 'compress.zlib://';
  const PROT_PHP_STDIN = 'php://stdin';
  const PROT_PHP_STDOUT = 'php://stdout';
  const PROT_PHP_STDERR = 'php://stderr';
  const PROT_PHP_OUTPUT = 'php://output';
  const PROT_PHP_INPUT = 'php://input';
  const PROT_PHP_FILTER = 'php://filter';
  const PROT_PHP_MEMORY = 'php://memory';
  const PROT_PHP_TEMP = 'php://temp';
  
  public static function file_get_contents($fileName, $throwException = true)
  {
    $contents = @file_get_contents($fileName);

    if (false === $contents && $throwException)
    {
      throw new Exception('Failed to read contents of file \''.$fileName.'\'.');
    }

    return $contents;
  }

  /**
   * Wrapper method for the php function file_put_contents with error handling.
   * Error handling in this case is optionally throwing an exception instead of
   * just returning false.
   *
   * @param string $fileName        The full path of the file to be writen to
   * @param string $data            The file contents
   * @param boolean $throwException Wheter to throw or not in the event of error
   * @return mixed                  Number of bytes written or false on error
   * @throw Exception
   */
  public static function file_put_contents($fileName, $data, $throwException = true)
  {
    $r = file_put_contents($fileName, $data);

    if (false === $r && $throwException)
    {
      throw new Exception('Failed to write file \''.$fileName.'\'.');
    }

    return $r;
  }

  public static function is_dir($dirName, $throwException = true)
  {
    $r = is_dir($dirName);

    if (false === $r && $throwException)
    {
      throw new Exception('Path \''.$dirName.'\' is not a directory.');
    }
    else
    {
      return $r;
    }
  }

  public static function is_readable($fileName, $throwException = true)
  {
    $r = is_readable($fileName);

    if (false === $r && $throwException)
    {
      throw new Exception('File \''.$fileName.'\' is not readable.');
    }
    else
    {
      return $r;
    }
  }

  public static function is_writable($fileName, $throwException = true)
  {
    $r = is_writable($fileName);

    if (false === $r && $throwException)
    {
      throw new Exception('File \''.$fileName.'\' is not writable.');
    }
    else
    {
      return $r;
    }
  }

  public static function file_exists($fileName, $throwException = true)
  {
    $r = file_exists($fileName);

    if (false === $r && $throwException)
    {
      throw new Exception('File \''.$fileName.'\' does not exist.');
    }
    else
    {
      return $r;
    }
  }

  /**
   * Wrapper for php mkdir() function that handles a few details and optionally
   * throws an exception if something fails.
   * If the directory already exists it simply returns true.
   * It will recursively build the path if it does not exist.
   *
   * @param string $dir             The directory name
   * @param integer $mode           The file mode (permissions)
   * @param boolean $throwExcpetion To throw or not to throw
   * @return boolean
   * @throw Exception
   */
  public static function mkdir($dir, $mode = 0777, $throwExcpetion = false)
  {
    if (self::file_exists($dir, false))
    {
      return true;
    }

    $currentUmask = umask(0); 
    $r = mkdir($dir, $mode, true);
    umask($currentUmask);

    if (false === $r && $throwException)
    {
      throw new Exception('Cannot make directory \''.$dir.'\'.');
    }
    else
    {
      return $r;
    }
  }
  
  public static function unlink($fileName, $throwException = true)
  {
    $r = @unlink($fileName);
    
    if (false === $r && $throwException)
    {
      throw new Exception('Failed to unlinke file \''.$fileName.'\'.');
    }
    else
    {
      return $r;
    }
  }

  public static function rename($oldFile, $newFile, $throwException = true)
  {
    if (self::file_exists($oldFile, $throwException))
    {
      $r = @rename($oldFile, $newFile);

      if (false === $r && $throwException)
      {
        throw new Exception('Failed to rename file \''.$oldFile.'\' to \''.$newFile.'\'.');
      }
      else
      {
        return $r;
      }
    }
    else
    {
      return false;
    }
  }

  public static function chmod($fileName, $mode, $throwException = true)
  {
    if (self::file_exists($fileName, $throwException))
    {
      $r = @chmod($fileName, $mode);

      if (false === $r && $throwException)
      {
        throw new Exception('chmod failed on file \''.$fileName.'\'.');
      }
      else
      {
        return $r;
      }
    }
    else
    {
      return false;
    }
  }

  public static function symlink($target, $linkName, $throwException = true)
  {
    if (function_exists('symlink'))
    {
      if (is_link($linkName))
      {
        if (readlink($linkName) != $target)
        {
          uFs::unlink($linkName, false);
        }
      }

      if (!self::relativeSymlink($target, $linkName))
      {
        if ($throwException)
        {
          throw new Exception('symlink('.$target.','.$linkName.') failed.');
        }
        else
        {
          return false;
        }
      }
    }
    else
    {
      if ($throwException)
      {
        throw new Exception('Function \'symlink\' does not exist.');
      }
      else
      {
        return false;
      }
    }
  }

  protected static function relativeSymlink($targetDir, $linkName)
  {
    $targetDir = self::calculateRelativeDir($linkName, $targetDir);

    return symlink($targetDir, $linkName);
  }

  protected static function calculateRelativeDir($from, $to)
  {
    $from = self::canonicalizePath($from);
    $to = self::canonicalizePath($to);

    $commonLength = 0;
    $minPathLength = min(strlen($from), strlen($to));

    // count how many chars the strings have in common
    for ($i = 0; $i < $minPathLength; $i++)
    {
      if ($from[$i] != $to[$i]) break;

      if ($from[$i] == self::DS) $commonLength = $i + 1;
    }

    if ($commonLength)
    {
      $levelUp = substr_count($from, self::DS, $commonLength);
      // up that many level
      $relativeDir  = str_repeat("..".self::DS, $levelUp);
      // down the remaining $to path
      $relativeDir .= substr($to, $commonLength);

      return $relativeDir;
    }

    return $to;
  }

  protected static function canonicalizePath($path)
  {
    if (empty($path)) return '';

    $out=array();

    foreach( explode(self::DS, $path) as $i => $fold)
    {
      if ($fold=='' || $fold=='.') continue;

      if ($fold=='..' && $i>0 && end($out)!='..')
      {
        array_pop($out);
      }
      else
      {
        $out[]= $fold;
      }
    }

    $result = $path{0} == self::DS ? self::DS : '';
    $result .= join(self::DS, $out);
    $result .= $path{strlen($path)-1} == self::DS ? self::DS : '';

    return $result;
  }

  /**
   * Wrapper function for fopen. This simply takes care of error handling for
   * the caller. If the calling code uses exceptions this method will reduce the
   * amount of code by taking care of the return value check and trowing an
   * exception if the parameter $throwException is true.
   * 
   * @param string $fileName        The name of the file to open
   * @param string $mode            File access mode (see doc fopen for details)
   * @param boolean $throwException If true, throw exception on failure
   * @return mixed boolean          false if fopen fails, otherwise the file resource
   */
  public static function fopen($fileName, $mode, $throwException = true)
  {
    $fp = fopen($fileName, $mode);

    if (false === $fp && $throwException)
    {
      throw new Exception('Cannot open '.$fileName.' for '.$mode.' access.');
    }
    else
    {
      return $fp;
    }
  }

  /**
   * Get a list of file in the $path and return it as an array. If a partial
   * file name is added at the end of the path then only files starting with
   * that partial name will be returned.
   * 
   * @param string $path            Full path
   * @param boolean $throwException Throw an exception on error if true
   * @return array                  List of full paths matching the cirteria
   * @throws Exception
   */
  public static function getListOfFiles($path, $throwException = true)
  {
    $list = array();
    $fileName = '';

    if (self::is_dir($path, false))
    {
      $basePath = $path;
    }
    else
    {
      $basePath = dirname($path);
      $fileName = basename($path);
    }

    self::is_dir($basePath, true);
    $iterator = new DirectoryIterator($basePath);

    while($iterator->valid())
    {
      $name = $iterator->getFilename();

      if (strlen($fileName) > 0)
      {
        $pattern = '/^'.$fileName.'/';

        if (1 === preg_match($pattern, $name))
        {
           $list[] = $basePath.self::DS.$name;
        }
      }
      else
      {
        $list[] = $basePath.self::DS.$name;
      }

      $iterator->next();
    }

    return $list;
  }

  /**
   * Helper method to build a path. This cuts down on the amount of code needed
   * whenever a path is put together.
   *
   * @param array $pathSegments Array of path segments
   * @return string             The built path
   */
  public static function makePath(array $pathSegments)
  {
    return implode(self::DS, $pathSegments);
  }

  /**
   * Alias for makePath, if you want it really short.
   *
   * @param array $pathSegments Array of path segments
   * @return string             The built path
   */
  public static function mp(array $pathSegments)
  {
    return self::makePath($pathSegments);
  }
}