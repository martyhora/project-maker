<?php

namespace App\Component;

use App\Model;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Nette\Application\UI\Form;

class ProjectForm extends \Nette\Application\UI\Control
{   
    /** @var Model\ProjectRepository */
    protected $projectRepository;

    /** @var Model\TransformRepository */
    protected $transformRepository;

    public function __construct(Model\ProjectRepository $projectRepository , Model\TransformRepository $transformRepository)
    {
        $this->projectRepository = $projectRepository;

        $this->transformRepository = $transformRepository;   
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/ProjectForm.latte');

        $this->template->render();
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentForm()
    {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer);

        $form->addText('title', 'Název projektu')->setAttribute('class', 'form-control')->addRule(Form::FILLED, "Pole 'Název projektu' je povinné.");

        $options = $this->transformRepository->findAll()->order('title')->fetchPairs('id', 'title');

        $form->addRadioList('transform_id', 'Transformace', $options)->addRule(Form::FILLED, "Pole 'Transformace' je povinné.");
        
        $form->addSubmit('save', ' Uložit ')->setAttribute('class', 'btn btn-primary btn-flat');
        $form->onSuccess[] = $this->projectFormSubmitted;

        return $form;
    }

    public function projectFormSubmitted(Form $form, $values)
    {                
        $this->projectRepository->save($values, $this->presenter->getParameter('id'));
        
        $this->presenter->flashMessage('Záznam byl uložen.', 'success');
        $this->presenter->redirect('Project:');
    }
}