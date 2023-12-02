<?php
namespace Migratool;

use Exception;
use PDO;
use PDOException;

class Migrator
{
    private string $directory = './migrations/';
    private PDO $db;
    private Result $result;

    public function __construct(PDO $db, string $directory = '')
    {
        $this->db = $db;
        $this->directory = realpath(
            (!empty($directory)) ? $directory : $this->directory
        );

        if (!is_readable($this->directory))
        {
            throw new Exception('Directory ' . $this->directory . ' is not readable');
        }

        $this->result = new Result;
    }

    public function run(): self
    {
        $files = $this->getFiles();
       
        foreach($files as $file)
        {
            $contents = $this->readFile($file);

            if (empty($contents))
            {
                $this->result->error($file, 'Empty contents');
                continue;
            }

            try {
                $this->execute($contents);
                $this->result->success($file);
            }
            catch (Exception $exception)
            {
                $this->result->error($file, $exception->getMessage());
            }
        }

        $this->result->save();

        return $this;
    }

    public function result(int $mode = 0): array
    {
        return $this->result->get($mode);
    }

    private function getFiles(): array
    {
        $data = [];

        $files = scandir($this->directory);
      
        foreach($files as $filename)
        {
            $filename = $this->directory . '/' . $filename;

            if (strrpos($filename, '.sql') === false 
            || $this->result->exists($filename, Result::STATUS_SUCCESS))
            {
                continue;
            }

            $data[] = $filename;
        }
        
        return $data;
    }

    private function readFile(string $filename): string
    {
        if (!is_readable($filename))
        {
            $this->result->error($filename, 'Not readable');

            return '';
        }

        $contents = file_get_contents($filename);
        
        return $contents;
    }

    private function execute(string $sql_query): bool
    {
        try 
        {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
            $this->db->beginTransaction();

            $query = $this->db->prepare($sql_query);
            $query->execute();

            $this->db->commit();
            
            return true;
        }
        catch (PDOException $exception)
        {
            $this->db->rollBack();
            
            throw new Exception($exception->getMessage());
        }
    }
}