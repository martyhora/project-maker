<?php

namespace App\Component;

use App\Model;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;

class TransformForm extends \Nette\Application\UI\Control
{   
    /** @var Model\TransformRepository */
    protected $transformRepository;

    public function __construct(Model\TransformRepository $transformRepository)
    {
        $this->transformRepository = $transformRepository;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/TransformForm.latte');

        $this->template->render();
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentForm()
    {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer);

        $form->addText('file', 'Balíček')->setAttribute('class', 'form-control')->addRule(Form::FILLED, "Pole 'Balíček' je povinné.");
        
        $form->addSubmit('save', ' Uložit ')->setAttribute('class', 'btn btn-primary btn-flat');
        $form->onSuccess[] = $this->transformFormSubmitted;

        return $form;
    }

    public function transformFormSubmitted(Form $form, $values)
    {                
        $this->transformRepository->save($values, $this->presenter->getParameter('id'));
        
        $this->presenter->flashMessage('Záznam byl uložen.', 'success');
        $this->presenter->redirect('Transform:');
    }
}