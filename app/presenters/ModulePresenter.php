<?php

namespace App\Presenters;

use App\Model;

use Nette\Application\BadRequestException;

class ModulePresenter extends BasePresenter
{
    /**
     * @var Model\ModuleRepository
     */
    protected $moduleRepository;

    public function __construct(Model\ModuleRepository $moduleRepository)
    {
        parent::__construct();

        $this->moduleRepository = $moduleRepository;
    }

    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }                
    }

    public function renderEdit($id)
    {
        $form = $this['moduleForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->moduleRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }                        
            
            $form->setDefaults($row);
        }
    }        
    
    public function actionDelete($id)
    {
        $this->moduleRepository->delete($id);
        
        $this->flashMessage('Záznam byl vymazán.', 'success');
        $this->redirect('Module:');                               
    }

    public function actionMake($id)
    {
        $folder = sha1(time());

        @mkdir(__DIR__ . '/../../temp/' . $folder);

        $this->createCrud($id, $folder);

        $this->zipCrud($folder);
    }

    public function actionMakeProject($id)
    {
        $folder = sha1(time());

        @mkdir(__DIR__ . '/../../temp/' . $folder);

        foreach ($this->moduleRepository->findAll()->where('project_id', $id) as $row) {
            $this->createCrud($row->id, $folder);
        }

        $this->zipCrud($folder);
    }

    protected function zipCrud($folder)
    {
        $zipname = __DIR__ . '/../../temp/project.zip';

        $rootPath = realpath(__DIR__ . '/../../temp/' . $folder);

        $zip = new \ZipArchive();
        $zip->open($zipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

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

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=' . basename($zipname));
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
    }

    protected function createCrud($id, $folder)
    {
        $row = $this->moduleRepository->findRow($id);
            
        if (!$row) {
            throw new BadRequestException();
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
    
    protected function createComponentModuleForm()
    {
        return $this->context->getService('moduleForm');
    }

    protected function createComponentModuleList()
    {
        return $this->context->getService('moduleList');
    }
}
