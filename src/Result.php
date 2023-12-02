<?php
namespace Migratool;

use Exception;

class Result
{
    const STATUS_ERROR = 0;
    const STATUS_SUCCESS = 1;
    const RETURN_ERR_ONLY = 2;

    private array $data;
    private array $errors;
    private array $current;

    private $filename = './result.json';

    public function __construct()
    {
        $this->checkFile();
        $this->data = [];

        $this->current = json_decode(file_get_contents($this->filename), true) ?? [];
    }

    public function success(string $filename)
    {
        $this->data[$filename] = self::STATUS_SUCCESS;
    }

    public function error(string $filename, string $err_msg)
    {
        $this->data[$filename] = self::STATUS_ERROR;
        $this->errors[$filename] = $err_msg;
    }

    public function exists($filename, int $mode): bool
    {
        return (!empty($this->current[$filename]) 
        && $this->current[$filename] === $mode);
    }

    public function get(int $mode = 0): array
    {
        $return = [];

        foreach ($this->data as $file => $result)
        {
            if ($mode === self::RETURN_ERR_ONLY 
            && $result === self::STATUS_SUCCESS)
            {
                continue;
            }

            $key = preg_replace('#.+\/(.+\.sql)#', '$1', $file);

            $return[$key] = (!empty($this->errors[$file])) 
                ? $this->errors[$file] 
                : 'Executed';
        }
        
        return $return;
    }

    public function save()
    {
        $final = array_merge($this->current, $this->data);
        
        file_put_contents($this->filename, json_encode($final));
    }

    private function checkFile()
    {
        if (!file_exists($this->filename))
        {
            file_put_contents($this->filename, '');
        }

        $this->filename = realpath($this->filename);

        if (empty($this->filename))
        {
            throw new Exception('Faild to determine result filename');
        }
        
        if (!is_readable($this->filename))
        {
            throw new Exception('Result file ' . $this->filename . ' is not readable');
        }

        if (!is_writable($this->filename))
        {
            throw new Exception('Result file ' . $this->filename . ' is not writable');
        }
    }
}