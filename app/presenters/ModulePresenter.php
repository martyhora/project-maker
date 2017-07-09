<?php

namespace App\Presenters;

use App\Model;
use App\Component;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\TextResponse;
use Nette\Database\Table\ActiveRow;

class ModulePresenter extends BasePresenter
{
    /** @var Model\ModuleRepository @inject */
    public $moduleRepository;

    /** @var Component\IModuleFormFactory @inject */
    public $moduleFormFactory;

    /** @var Component\IModuleListFactory @inject */
    public $moduleListFactory;

    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Auth:login');
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

        $httpResponse = $this->getHttpResponse();
        $httpResponse->setContentType('application/zip');
        $httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . basename($zipname) . '"');
        $httpResponse->setHeader('Content-Length', filesize($zipname));

        $this->sendResponse(new TextResponse(readfile($zipname)));
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
        $component = $this->moduleFormFactory->create($this->getParameter('id'));

        $component->onModuleSave[] = function(Component\ModuleForm $form, ActiveRow $module) {
            $this->presenter->flashMessage('Záznam byl uložen.', 'success');
            $this->presenter->redirect('Module:');
        };

        return $component;
    }

    protected function createComponentModuleList()
    {
        return $this->moduleListFactory->create();
    }
}
