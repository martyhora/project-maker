<?php

namespace App\Component;

use App\Model;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Nette\Application\UI\Form;

class ProjectForm extends \Nette\Application\UI\Control
{   
    /** @var Model\ProjectRepository */
    protected $projectRepository;

    /** @var int */
    private $projectId;

    /** @var callable  */
    public $onProjectSave;

    public function __construct($projectId, Model\ProjectRepository $projectRepository)
    {
        parent::__construct();

        $this->projectId = $projectId;

        $this->projectRepository = $projectRepository;
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

        $form->addRadioList('transformation', 'Transformace', Model\ModuleRepository::$transformations)->addRule(Form::FILLED, "Pole 'Transformace' je povinné.");
        
        $form->addSubmit('save', ' Uložit ')->setAttribute('class', 'btn btn-primary btn-flat');
        $form->onSuccess[] = [$this, 'processFormValues'];

        return $form;
    }

    public function processFormValues(Form $form, $values)
    {
        $project = $this->projectRepository->save($values, $this->projectId);

        $this->onProjectSave($this, $project);
    }
}

interface IProjectFormFactory
{
    /**
     * @param int $projectId
     * @return ProjectForm
     */
    public function create($projectId);
}