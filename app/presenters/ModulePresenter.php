<?php

namespace App\Presenters;

use App\Exception\TransformationException;
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
        $zipFilename = $this->moduleRepository->makeCrud($id);

        if ($zipFilename === false) {
            throw new BadRequestException();
        }

        $this->downloadZippedCrud($zipFilename);
    }

    public function actionMakeProject($id)
    {
        try {
            $this->downloadZippedCrud($this->moduleRepository->makeProject($id));
        } catch (TransformationException $e) {
            $this->flashMessage($e->getMessage(), 'danger');
            $this->redirect('Project:');
        }
    }

    private function downloadZippedCrud($zipFilename)
    {
        $httpResponse = $this->getHttpResponse();
        $httpResponse->setContentType('application/zip');
        $httpResponse->setHeader('Content-Disposition', 'attachment; filename="' . basename($zipFilename) . '"');
        $httpResponse->setHeader('Content-Length', filesize($zipFilename));

        $this->sendResponse(new TextResponse(readfile($zipFilename)));
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
