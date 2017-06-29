<?php

namespace App\Component;

use App\Model;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Nette\Application\UI\Form;

class CrudForm extends \Nette\Application\UI\Control
{   
    /** @var Model\Crud */
    protected $crudRepository;

    /** @additionalComponentDependencyDeclaration */

    public function __construct(Model\Crud $crudRepository /** @additionalComponentDependencyHint */)
    {
        $this->crudRepository = $crudRepository;

        /** @additionalComponentDependencyAssertion */   
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/CrudForm.latte');

        $this->template->render();
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentForm()
    {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer);

/** @formFields */
        
        $form->addSubmit('save', ' Uložit ')->setAttribute('class', 'btn btn-primary btn-flat');
        $form->onSuccess[] = $this->crudFormSubmitted;

        return $form;
    }

    public function crudFormSubmitted(Form $form, $values)
    {                
        $this->crudRepository->save($values, $this->presenter->getParameter('id'));
        
        $this->presenter->flashMessage('Záznam byl uložen.', 'success');
        $this->presenter->redirect('Crud:');
    }
}