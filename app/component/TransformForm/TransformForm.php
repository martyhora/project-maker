<?php

namespace App\Component;

use App\Model;
use Nette\Application\UI\Form;
use Tomaj\Form\Renderer\BootstrapRenderer;

class TransformForm extends \Nette\Application\UI\Control
{   
    /** @var Model\TransformRepository */
    protected $transformRepository;

    /** @var int */
    private $transformId;

    /** @var callable  */
    public $onTransformSave;

    public function __construct($transformId, Model\TransformRepository $transformRepository)
    {
        parent::__construct();

        $this->transformId = $transformId;

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
        $form->onSuccess[] = [$this, 'processFormValues'];

        return $form;
    }

    public function processFormValues(Form $form, $values)
    {
        $transform = $this->transformRepository->save($values, $this->transformId);

        $this->onTransformSave($this, $transform);
    }
}

interface ITransformFormFactory
{
    /**
     * @param int $transformId
     * @return TransformForm
     */
    public function create($transformId);
}