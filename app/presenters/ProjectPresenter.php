<?php

namespace App\Presenters;

use App\Model;

use Nette\Application\BadRequestException;

class ProjectPresenter extends BasePresenter
{
    /**
     * @var Model\ProjectRepository
     */
    protected $projectRepository;

    public function __construct(Model\ProjectRepository $projectRepository)
    {
        parent::__construct();

        $this->projectRepository = $projectRepository;
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
        $form = $this['projectForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->projectRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }                        
            
            $form->setDefaults($row);
        }
    }        
    
    public function actionDelete($id)
    {
        $this->projectRepository->delete($id);
        
        $this->flashMessage('Záznam byl vymazán.', 'success');
        $this->redirect('Project:');                               
    }
    
    protected function createComponentProjectForm()
    {
        return $this->context->getService('projectForm');
    }

    protected function createComponentProjectList()
    {
        return $this->context->getService('projectList');
    }
}
