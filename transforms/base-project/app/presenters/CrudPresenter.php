<?php

namespace App\Presenters;

use Nette,
    App\Model;

use Nette\Application\UI\Form;
use Nette\Application\BadRequestException;

class CrudPresenter extends BasePresenter
{
    /**
     * @var Model\Crud
     */
    protected $crudRepository;

    public function __construct(Model\Crud $crudRepository)
    {
        parent::__construct();

        $this->crudRepository = $crudRepository;
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
        return $this->context->getService('crudForm');
    }

    protected function createComponentCrudList()
    {
        return $this->context->getService('crudList');
    }
}
