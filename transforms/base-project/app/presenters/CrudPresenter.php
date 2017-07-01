<?php

namespace App\Presenters;

use App\Model;
use App\Component;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class CrudPresenter extends BasePresenter
{
    /** @var Model\Crud @inject */
    public $crudRepository;

    /** @var Component\ICrudFormFactory @inject */
    public $crudFormFactory;

    /** @var Component\ICrudListFactory @inject */
    public $crudListFactory;

    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }                
    }

    public function renderEdit($id)
    {
        $form = $this['crudForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->crudRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }                        
            
            $form->setDefaults($row);
        }
    }        
    
    public function actionDelete($id)
    {
        $this->crudRepository->delete($id);
        
        $this->flashMessage('Záznam byl vymazán.', 'success');
        $this->redirect('Crud:');                               
    }

    protected function createComponentCrudForm()
    {
        $component = $this->crudFormFactory->create($this->getParameter('id'));

        $component->onCrudSave[] = function(Component\CrudForm $form, ActiveRow $crud) {
            $this->presenter->flashMessage('Záznam byl uložen.', 'success');
            $this->presenter->redirect('Crud:');
        };

        return $component;
    }

    protected function createComponentCrudList()
    {
        return $this->crudListFactory->create();
    }
}
