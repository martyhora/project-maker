<?php

namespace App\Presenters;

use App\Model;
use App\Component;
use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class EstatePresenter extends BasePresenter
{
    /** @var Model\EstateRepository @inject */
    public $estateRepository;

    /** @var Component\IEstateFormFactory @inject */
    public $estateFormFactory;

    /** @var Component\IEstateListFactory @inject */
    public $estateListFactory;

    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }                
    }

    public function renderEdit($id)
    {
        $form = $this['estateForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->estateRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }                        
            
            $form->setDefaults($row);
        }
    }        
    
    public function actionDelete($id)
    {
        $this->estateRepository->delete($id);
        
        $this->flashMessage('Záznam byl vymazán.', 'success');
        $this->redirect('Estate:');                               
    }

    protected function createComponentEstateForm()
    {
        $component = $this->estateFormFactory->create($this->getParameter('id'));

        $component->onEstateSave[] = function(Component\EstateForm $form, ActiveRow $estate) {
            $this->presenter->flashMessage('Záznam byl uložen.', 'success');
            $this->presenter->redirect('Estate:');
        };

        return $component;
    }

    protected function createComponentEstateList()
    {
        return $this->estateListFactory->create();
    }
}
