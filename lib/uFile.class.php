<?php
/**
 * Helper class that simplifies varios file and path operations. Mostly this
 * wraps existing php functions but provides them all within a class context.
 *
 * @package     uUtilitiesPlugin
 * @subpackage  uFile
 * @author      Henning Glatter-Gotz <henning@glatter-gotz.com>
 */
class uFile
{
  protected $fullPath;
  protected $dirname;
  protected $basename;
  protected $extension;
  protected $filename;

  public function __construct($fullPath)
  {
    $this->setFileName($fullPath);
  }

  /**
   * Return the entire full path of the file.
   *
   * @return string
   */
  public function getFullPath()
  {
    return $this->fullPath;
  }

  /**
   * Retrun only the directory name without the file name.
   *
   * @return string
   */
  public function getDirName()
  {
    return $this->dirname;
  }

  /**
   * Return the file name without the path portion.
   *
   * @return string
   */
  public function getBaseName()
  {
    return $this->basename;
  }

  /**
   * Return the extension of the file.
   *
   * @return string
   */
  public function getExtension()
  {
    return $this->extension;
  }

  /**
   * Return the file name without the extension.
   *
   * @return string
   */
  public function getFileName()
  {
    return $this->filename;
  }

  /**
   * Uncompress a file that is compressed. Currenly only supports gzip.
   *
   * @param boolean $deleteCompressed If true, delete the original compressed
   *                                  file
   * @return string                   The full path of the uncompressed file
   */
  public function uncompress($deleteCompressed = false)
  {
    $oldFullPath = $this->fullPath;

    switch ($this->extension)
    {
      case 'gz':
        $contents = uFs::file_get_contents(uFs::PROT_ZLIB.$this->fullPath);
        $newFullPath = $this->dirname.uFs::DS.$this->filename;
        $this->setFileName($newFullPath);
        uFs::file_put_contents($newFullPath, $contents);
        break;
      default:
        throw new Exception($this->extension.' is not a supported compression format.');
        break;
    }

    if ($deleteCompressed)
    {
      uFs::unlink($oldFilePath);
    }

    return $newFullPath;
  }

  /**
   * Compress a file using gzip.
   *
   * @param string $compressionFormat   Compression algoritm to use for
   *                                    compression
   * @param boolean $deleteUncompressed If true, delete the original
   *                                    uncompressed file
   * @return string
   */
  public function compress($compressionFormat, $deleteUncompressed = false)
  {
    $oldFullPath = $this->fullPath;

    switch ($compressionFormat)
    {
      case uFs::PROT_ZLIB:
        if ($this->extension == 'gz')
        {
          throw new Exception('File already compressed!');
        }

        $contents = uFs::file_get_contents($this->fullPath);
        $newFullPath = $this->fullPath.'.gz';
        $this->setFileName($newFullPath);
        uFs::file_put_contents(uFs::PROT_ZLIB.$newFullPath, $contents);
        break;
      default:
        throw new Exception($compressionFormat.' is not a supported compression format.');
        break;
    }

    if ($deleteUncompressed)
    {
      uFs::unlink($oldFullPath);
    }

    return $newFullPath;
  }

  /**
   * Copy a file
   *
   * @param string $newFullPath The full path of the copied file
   * @return string
   */
  public function copy($newFullPath)
  {
    $contents = uFs::file_get_contents($this->fullPath);
    uFs::file_put_contents($newFullPath, $contents);
    $this->setFileName($newFullPath);

    return $newFullPath;
  }

  private function setFileName($fullPath)
  {
    $this->fullPath = $fullPath;
    $pi = pathinfo($fullPath);
    $this->dirname = $pi['dirname'];
    $this->basename = $pi['basename'];
    $this->extension = $pi['extension'];
    $this->filename = $pi['filename'];
  }

  private function unzipFile($fullPath)
  {
    /*
    if ($this->extension == 'zip')
    {
      uFs::is_writable($tmpDir, true);
      $fullPath = $tmpDir.DIRECTORY_SEPARATOR.$this->filename;
      uFs::file_put_contents($fullPath, $this->content, true);

      $zipArchive = new ZipArchive;

      if ($zipArchive->open($fullPath))
      {
        $zip_stats = $zipArchive->statIndex(0);
        $realFileName = $zip_stats['name'];

        if (!$zipArchive->extractTo($tmpDir, $realFileName))
        {
          unlink($fullPath);
          throw new Exception('Unzip of '.$fullPath.' failed.');
        }

        $fileInfo = pathinfo($realFileName);
        $this->uncompressedFileName = $realFileName;
        $this->uncompressedFileExtension = $fileInfo['extension'];

        $realFileName = $tmpDir.DIRECTORY_SEPARATOR.$realFileName;
        $zipArchive->close();
        unlink($fullPath);
        $content = uFs::file_get_contents($realFileName, true);
        unlink($realFileName);

        if (false === $content)
        {
          throw new Exception('Failed to read content of '.$realFileName);
        }

        return $content;
      }
    }
    */
  }
}

/**
 * Temp file helper class. This class will allow for creation of a temporary
 * file that will be automatically delete when the object goes out of scope.
 * Can be thought of self cleanup.
 */
class uTempFile extends uFile
{
  public function __construct($fullPath)
  {
    parent::__construct($fullPath);
  }
  
  public function __destruct()
  {
    uFs::unlink($this->fullPath);
  }
}