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

    /** @var int */
    private $projectId;

    /** @var callable  */
    public $onProjectSave;

    public function __construct($projectId, Model\ProjectRepository $projectRepository, Model\TransformRepository $transformRepository)
    {
        parent::__construct();

        $this->projectId = $projectId;

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