<?php

namespace App\Model;

class ModuleRepository extends BaseRepository
{	   
    protected $tableName = 'module';

    protected $filterColumns = [
        'like'  => ['name', 'title', 'date_created'],
        'equal' => ['project_id'],
    ];

    public function makeCrud($id)
    {
        $folder = $this->makeTempFolder();

        if ($this->createCrud($id, $folder) === false) {
            return false;
        }

        return $this->zipCrud($folder);
    }

    public function makeProject($id)
    {
        $folder = $this->makeTempFolder();

        foreach ($this->findAll()->where('project_id', $id) as $row) {
            $this->createCrud($row->id, $folder);
        }

        return $this->zipCrud($folder);
    }

    private function makeTempFolder()
    {
        $folder = sha1(time());

        @mkdir(__DIR__ . '/../../temp/' . $folder);

        return $folder;
    }

    private function zipCrud($folder)
    {
        $zipFilename = __DIR__ . '/../../temp/project.zip';

        $rootPath = realpath(__DIR__ . '/../../temp/' . $folder);

        $zip = new \ZipArchive();
        $zip->open($zipFilename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            if (!$file->isDir())
            {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                $zip->addFile($filePath, $relativePath);
            }
        }

        $zip->close();

        return $zipFilename;
    }

    private function createCrud($id, $folder)
    {
        $row = $this->findRow($id);

        if (!$row) {
            return false;
        }

        require_once "transforms/{$row->project->transform->url}/{$row->project->transform->class_name}.php";

        $class = "\\{$row->project->transform->class_name}\\{$row->project->transform->class_name}";

        $config = [
            'title'  => $row->title,
            'name'   => $row->name,
            'fields' => json_decode($row->params, true),
        ];

        $maker = new $class;

        $maker->setProjectName($row->project->title);

        $maker->setBuildPath(__DIR__ . '/../../temp/' . $folder);
        $maker->setTransformPath(__DIR__ . '/../../transforms/' . $row->project->transform->url . '/');
        $maker->setConfig($config);

        // $maker->clean();

        $maker->make(true);
    }
}
