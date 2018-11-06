<?php

//require constant("APP_PATH").'/core/App.php';
class PhpParsar extends App
{
  private $path;
  private $first_index_filepath;
  private $needles;
  public $app_path;
  private $pointer_value;
  private $number_of_paths_found;
  private $itteration_level;

  public function __construct($path)
  {
    $this->path = $path;
    /*$this->itteration_level=0;
    $this->pointer_value=0;
    if (count($this->path ) > 0)
    {
      $this->number_of_paths_found=1;
    }*/
    $this->app_path = constant('APP_PATH');
    $this->needles = ['require_once', 'include_once','include','require'];
    echo "Starting...\n";
    //$this->check_if_unchecked_paths_exist();
  }

  public function check_if_unchecked_paths_exist()
  {
    $srces = $this->read_path_to_follow();
    foreach($srces as $src )
    {
      var_dump($src);
    }
    die();
  }

  public function start()
  {
      $this->parse();
  }

  /**
  * Read the file that has the pahs to be followed
  * Returns array
  **/
  private function read_path_to_follow()
  {
    $str = @file_get_contents($this->app_path.'/app/data/pathstofollow.json');
    return json_decode($str, true);
  }

  private function parse()
  {
    if ($this->check_path_existance())
    {
      echo "Path: ".$this->path." exists.\n";
    } else {
      echo "Path: ".$this->path." does not exist. Exiting... \n";
      exit;
    }

    if (!$this->check_index_existance())
    {
      echo "index.php file does not exist in the path: ".$this->path.". Exiting... \n";
      exit;
    }
    $lines = [];
    $lines = $this->get_lines();


  }

  private function check_path_existance()
  {
    return file_exists($this->path);
  }

  private function check_index_existance()
  {
    //find /Users/home/phpproj/test1 -name "index*" -print
    exec("find $this->path -name \"index.*\"", $arr, $stat);

    if (count($arr) > 0) {
      $this->first_index_filepath = $arr[0];
      return 1;
      //TODO handle more than one
    }

    return 0;
  }

  private function get_lines()
  {
      $handle = fopen($this->first_index_filepath, "r");
      $text=fread($handle,filesize($this->first_index_filepath));
      $lines=explode(PHP_EOL,$text);
      $paths_to_folow =[];
      $count = 0;
      foreach($lines as $line)
      {
        if ($this->check_if_needles_exist($line) === 1)
        {
          $paths_to_folow[$this->first_index_filepath][] =
          [
                  "path_$count" => $this->get_value_from_needle($line)
          ];
        }
        $count++;
      }
      fclose($handle);
      $this->write_path_to_follow($paths_to_folow);
  }

  private function check_if_needles_exist($line)
  {
      foreach ($this->needles as $needle)
      {
        $pos = strpos($line, $needle);
        if ($pos !== false)
        {
          return 1;
        }
      }

    return 0;
  }

  private function get_value_from_needle($line)
  {
    //$matches = preg_quote('/()\'\")/', $line);
    $str = str_replace('\'','',$line);
    $str = str_replace('"','',$str);
    $str = str_replace('(','',$str);
    $str = str_replace(')','',$str);
    foreach ($this->needles as $needle)
    {
      $str = str_replace($needle.' ','',$str);
    }
    $str = trim($str);
    return $str;
  }

  private function write_path_to_follow($paths_to_folow)
  {
    $file = fopen($this->app_path.'/app/data/pathstofollow.json', "w");
    fwrite($file,json_encode($paths_to_folow));
    $this->number_of_paths_found += 1;
  }
}