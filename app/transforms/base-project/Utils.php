<?php

namespace Transform;

class Utils
{
    public function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];

        foreach ($ret as &$match)
        {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }

        return implode('_', $ret);
    }

    function underscoreToCamelCase($string, $isFirstCharCaps = false)
    {
        if ($isFirstCharCaps === true)
        {
            $string[0] = strtoupper($string[0]);
        }

        $func = create_function('$c', 'return strtoupper($c[1]);');

        return preg_replace_callback('/_([a-z])/', $func, $string);
    }

    public function deleteFolder($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file)
        {
            (is_dir("{$dir}/{$file}")) ? $this->deleteFolder("{$dir}/{$file}") : unlink("{$dir}/{$file}");
        }

        return rmdir($dir);
    }

    function copyFolder($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);

        while (false !== ($file = readdir($dir)))
        {
            if (($file !== '.') && ($file !== '..'))
            {
                if (is_dir($src . '/' . $file))
                {
                    $this->copyFolder($src . '/' . $file, $dst . '/' . $file);
                }
                else
                {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);
    }
}