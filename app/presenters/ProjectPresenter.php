<?php

namespace App\Presenters;

use App\Model;
use App\Component;

use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class ProjectPresenter extends BasePresenter
{
    /** @var Model\ProjectRepository @inject */
    public $projectRepository;

    /** @var Component\IProjectFormFactory @inject */
    public $projectFormFactory;

    /** @var Component\IProjectListFactory @inject */
    public $projectListFactory;

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
        $component = $this->projectFormFactory->create($this->getParameter('id'));

        $component->onProjectSave[] = function(Component\ProjectForm $form, ActiveRow $project) {
            $this->presenter->flashMessage('Záznam byl uložen.', 'success');
            $this->presenter->redirect('Project:');
        };

        return $component;
    }

    protected function createComponentProjectList()
    {
        return $this->projectListFactory->create();
    }
}
