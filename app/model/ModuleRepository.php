<?php

namespace App\Model;

use App\Exception\TransformationException;
use Transform\BaseProjectTransformation;
use Transform\ITransformation;

class ModuleRepository extends BaseRepository
{	   
    const TRANSFORMATION_BASE_NETTE_PROJECT = 'baseNetteProject';

    protected $tableName = 'module';

    public static $transformations = [
        self::TRANSFORMATION_BASE_NETTE_PROJECT => 'Základní Nette projekt',
    ];

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

        $config = [
            'title'  => $row->title,
            'name'   => $row->name,
            'fields' => json_decode($row->params, true),
        ];

        $transformation = $this->getTransformation($row->project->transformation);

        if ($transformation === null) {
            throw new TransformationException("Transformace {$row->project->transformation} nebyla nalezena.");
        }

        $transformation->setProjectName($row->project->title);

        $transformation->setBuildPath(__DIR__ . '/../../temp/' . $folder);
        $transformation->setConfig($config);

        // $this->transform->clean();

        $transformation->make(true);

        return true;
    }

    /**
     * @param string $transformation
     *
     * @return ITransformation
     */
    private function getTransformation($transformation)
    {
        $resultTransformation = null;

        switch ($transformation) {
            case self::TRANSFORMATION_BASE_NETTE_PROJECT:
                $resultTransformation = new BaseProjectTransformation();
                break;
        }

        return $resultTransformation;
    }
}
