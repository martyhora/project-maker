<?php

namespace App\Presenters;

use App\Model;
use App\Component;

use Nette\Application\BadRequestException;
use Nette\Database\Table\ActiveRow;

class TransformPresenter extends BasePresenter
{
    /** @var Model\TransformRepository @inject */
    public $transformRepository;

    /** @var Component\ITransformFormFactory @inject */
    public $transformFormFactory;

    /** @var Component\ITransformListFactory @inject */
    public $transformListFactory;


    public function __construct(Model\TransformRepository $transformRepository)
    {
        parent::__construct();

        $this->transformRepository = $transformRepository;
    }

    protected function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Auth:login');
        }                
    }

    public function renderEdit($id)
    {
        $form = $this['transformForm']['form'];
        
        if (!$form->isSubmitted()) {
            $row = $this->transformRepository->findRow($id);
            
            if (!$row) {
                throw new BadRequestException();
            }                        
            
            $form->setDefaults($row);
        }
    }        
    
    public function actionDelete($id)
    {
        $this->transformRepository->delete($id);
        
        $this->flashMessage('Záznam byl vymazán.', 'success');
        $this->redirect('Transform:');                               
    }

    protected function createComponentTransformForm()
    {
        $component = $this->transformFormFactory->create($this->getParameter('id'));

        $component->onTransformSave[] = function(Component\TransformForm $form, ActiveRow $transform) {
            $this->presenter->flashMessage('Záznam byl uložen.', 'success');
            $this->presenter->redirect('Transform:');
        };

        return $component;
    }

    protected function createComponentTransformList()
    {
        return $this->transformListFactory->create();
    }
}
