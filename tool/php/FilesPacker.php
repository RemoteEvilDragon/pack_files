<?php

require_once(__DIR__ . '/init.php');
require_once(__DIR__ . '/xxtea.php');

class FilesPacker
{
    private $config;
    private $options;
    private $validated = false;

    function __construct($config, $options)
    {
        $this->config = $config;
        $this->options = $options;
    }

    function validateConfig()
    {
        if (empty($this->config['src']))
        {
            printf("ERR: not specifies source files directory\n");
            return false;
        }

        if (empty($this->config['output']))
        {
            printf("ERR: not output filename or output directory\n");
            return false;
        }

        if (empty($this->config['key']))
        {
           print("ERR: not set encrypt key\n");
           return false;
        }

        if (!empty($this->config['prefix']))
        {
            $this->config['prefix'] = $this->config['prefix'] . '.';
        }
        else
        {
            $this->config['prefix'] = '';
        }

        // check src path
        $srcpath = realpath($this->config['src']);
        if (!is_dir($srcpath))
        {
            printf("ERR: invalid src dir %s\n", $srcpath);
            return false;
        }
        $this->config['srcpath'] = $srcpath;
        $this->config['srcpathLength'] = strlen($srcpath) + 1;

        // check output path
        @mkdir($this->config['output'], 0777, true);
        $this->config['output'] = realpath($this->config['output']);
        if (empty($this->config['output']) || !is_dir($this->config['output']))
        {
            printf("ERR: invalid output dir %s\n", $this->config['output']);
            return false;
        }

        $this->validated = true;
        return true;
    }

    function run()
    {
        if (!$this->validated)
        {
            print("ERR: invalid config\n");
            return false;
        }

        $files = $this->searchSourceFiles();
        $modules = $this->prepareForPack($files);

        $bytes = $this->getModulesData($modules, $this->config['key']);

        if (!is_array($bytes))
        {
            $this->cleanupTempFiles($modules);
            return false;
        }

        if (!$this->createOutputFiles($modules, $bytes))
        {
            $this->cleanupTempFiles($modules);
            return false;
        }

        $this->cleanupTempFiles($modules);
        return true;
    }

    protected function searchSourceFiles()
    {
        printf("Pack source files in path %s\n", $this->config['srcpath']);
        $files = array();
        findFiles($this->config['srcpath'], $files);
        return $files;
    }

    protected function prepareForPack(array $files)
    {
        $modules = array();
        // prepare for pack
        foreach ($files as $key => $path)
        {
            $fileName = $moduleName = substr($path, $this->config['srcpathLength']);
            $moduleName = str_replace('.', SPLIT_CHAR, $moduleName);
            $tempFilePath = $this->config['srcpath'] . DS . $moduleName . '.tmp';
            $moduleName = str_replace(DS, '.', $moduleName);

            if (isset($this->config['ext']))
            {
                $dotIdx = stripos($fileName, '.');
                if ($dotIdx !== false)
                {
                    $fileName = substr($fileName, 0, $dotIdx);
                    if ($this->config['ext'][0] == '.') 
                    {
                        $fileName = $fileName . $this->config['ext'];
                    }
                    else
                    {
                        $fileName = $fileName . '.' . $this->config['ext'];
                    }
                }
            }

            $modules[$path] = array(
                'moduleName' => $moduleName,
                'outputName' => $fileName,
                'tempFilePath' => $tempFilePath,
            );
        }
        return $modules;
    }

    protected function cleanupTempFiles(array $modules)
    {
        foreach ($modules as $module)
        {
            if (file_exists($module['tempFilePath']))
            {
                unlink($module['tempFilePath']);
            }
        }
    }

    protected function getModulesData(array $modules, $key = null)
    {
        if (!empty($key))
        {
            $xxtea = new XXTEA();
            $xxtea->setKey($key);
        }

        $modulesBytes = array();
        foreach ($modules as $path => $module)
        {
            $bytes = file_get_contents($path);
            if (!empty($key))
            {
                $bytes = $xxtea->encrypt($bytes);
            }
            file_put_contents($module['tempFilePath'], $bytes);
            
            if (!$bytes)
            {
                print("\n");
                return false;
            }
            $modulesBytes[$path] = $bytes;
            printf("  > get bytes [% 4d KB] %s\n", ceil(strlen($bytes) / 1024), $module['outputName']);
        }
        return $modulesBytes;
    }

    protected function createOutputFiles(array $modules, array $bytes)
    {
        foreach ($modules as $module)
        {
            $destPath = $this->config['output'] . DS . $this->config['prefix'] . $module['outputName'];
            @mkdir(pathinfo($destPath, PATHINFO_DIRNAME), 0777, true);
            rename($module['tempFilePath'], $destPath);
        }

        printf("create output files in %s .\n", $this->config['output']);
        print("done.\n\n");
        return true;
    }
}
