<?php

namespace App\Presenters;

use App\Model;

use Nette\Application\BadRequestException;

class TransformPresenter extends BasePresenter
{
    /**
     * @var Model\TransformRepository
     */
    protected $transformRepository;

    public function __construct(Model\TransformRepository $transformRepository)
    {
        parent::__construct();

        $this->transformRepository = $transformRepository;
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
        return $this->context->getService('transformForm');
    }

    protected function createComponentTransformList()
    {
        return $this->context->getService('transformList');
    }
}
