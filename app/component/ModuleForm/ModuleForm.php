<?php

namespace App\Component;

use App\Model;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Nette\Application\UI\Form;

class ModuleForm extends \Nette\Application\UI\Control
{   
    /** @var Model\ModuleRepository */
    protected $moduleRepository;

    /** @var Model\ProjectRepository */
    protected $projectRepository;

    /** @var int */
    private $moduleId;

    /** @var callable  */
    public $onModuleSave;

    public function __construct($moduleId, Model\ModuleRepository $moduleRepository, Model\ProjectRepository $projectRepository)
    {
        parent::__construct();

        $this->moduleId = $moduleId;

        $this->moduleRepository = $moduleRepository;

        $this->projectRepository = $projectRepository;   
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/ModuleForm.latte');

        $this->template->render();
    }

    /**
     * @return \Nette\Application\UI\Form
     */
    protected function createComponentForm()
    {
        $form = new Form();
        $form->setRenderer(new BootstrapRenderer);

        $form->addText('name', 'Název modulu')->setAttribute('class', 'form-control');

        $form->addText('title', 'Titulek modulu')->setAttribute('class', 'form-control');

        $options = $this->projectRepository->findAll()->order('title')->fetchPairs('id', 'title');

        $form->addSelect('project_id', 'Projekt', $options)->setPrompt('- Vyberte -')->setAttribute('class', 'form-control');

        $form->addTextarea('params', 'Parametry v JSONu')->setAttribute('class', 'form-control')->setAttribute('style', 'height: 300px');
        
        $form->addSubmit('save', ' Uložit ')->setAttribute('class', 'btn btn-primary btn-flat');

        $form->onSuccess[] = [$this, 'processFormValues'];

        return $form;
    }

    public function processFormValues(Form $form, $values)
    {
        $module = $this->moduleRepository->save($values, $this->moduleId);

        $this->onModuleSave($this, $module);
    }
}

interface IModuleFormFactory
{
    /**
     * @param int $moduleId
     * @return ModuleForm
     */
    public function create($moduleId);
}