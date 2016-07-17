<?php

/**
 * @package IniParser 1.0
 * @author Eduardo Arruda <http://www.webgit.com.br/>
 * @since 11/06/2016
 * @version 1.0
 *
 * IMPORTANT:
 * - Remember to change the default dir on the constructor.
 */
class IniParser
{

    private $dir;
    private $method;
    private $path;

    /**
     * IniParser constructor.
     * @param bool $return_object
     */
    public function __construct($return_object = false)
    {
        $this->dir = "./accounts/";
        $this->method = $return_object;
    }


    /**
     * function for file validation.
     *
     * @param $file_name
     * @return bool
     */
    public function fileExists($file_name)
    {
        $this->path = "{$this->dir}{$file_name}.ini";
        if (file_exists($this->path) && !is_dir($this->path))
        {
            return true;
        }
    }


    /**
     * function to generate file.
     *
     * @param $file_name
     * @return array|object
     */
    public function getFileContent($file_name)
    {
        $this->path = "{$this->dir}{$file_name}.ini";
        if ($this->fileExists($file_name))
        {
            $res = array();
            $parse_arr = parse_ini_file($this->path, false);
            foreach($parse_arr as $key => $val)
            {
                $res[$key] = is_numeric($val) ? intval($val) : $val;
            }
            return ($this->method) ? (object) $res : $res;
        }
    }


    /**
     * function to delete file.
     *
     * @param $file_name
     * @return bool
     */
    public function deleteFile($file_name)
    {
        $this->path = "{$this->dir}{$file_name}.ini";
        if ($this->FileExists($file_name))
        {
            if (unlink($this->path))
            {
                return true;
            }
        }
    }


    /**
     * function to update file.
     *
     * @param $array
     * @param $file_name
     */
    public function updateFile($array, $file_name)
    {
        $this_ac = $this->getFileContent($file_name, true);
        if($this_ac)
        {
            $res = array();
            $keys = array();

            foreach($array as $key => $val)
            {
                $res[] = "$key = " . (is_numeric($val) ? $val : '"' . $val . '"');
                $keys[] = $key;
            }
            $keys_string  = implode($keys, '/');
            foreach($this_ac as $a_key => $a_val)
            {
                if(strpos($keys_string, $a_key) === false)
                {
                    $res[] = "$a_key = " . (is_numeric($a_val) ? $a_val : '"' . $a_val . '"');
                }
            }
            $this->saveFile($file_name, implode("\r\n", $res));
        }
    }


    /**
     * function to create file.
     *
     * @param $array
     * @param $file_name
     */
    public function createFile($array, $file_name)
    {
        $res = array();
        foreach($array as $key => $val)
        {
            $res[] = "$key = " . (is_numeric($val) ? $val : '"' . $val . '"');
        }
        $this->saveFile($file_name, implode("\r\n", $res));
    }


    /**
     * function to save file.
     * 
     * @param $file_name
     * @param $dataToSave
     */
    private function saveFile($file_name, $dataToSave)
    {
        $this->path = "{$this->dir}{$file_name}.ini";
        if ($fp = fopen($this->path, 'w'))
        {
            $startTime = microtime(TRUE);
            do
            {
                $canWrite = flock($fp, LOCK_EX);
                if (!$canWrite) usleep(round(rand(0, 100) * 1000));
            }
            while ((!$canWrite) and ((microtime(TRUE) - $startTime) < 5));
            if ($canWrite)
            {
                fwrite($fp, $dataToSave);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }
}
