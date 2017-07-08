<?php

namespace App\Component;

use App\Model;
use Tomaj\Form\Renderer\BootstrapRenderer;
use Nette\Application\UI\Form;

class CrudForm extends \Nette\Application\UI\Control
{   
    /** @var Model\CrudRepository */
    protected $crudRepository;

    /** @var int */
    private $crudId;

    /** @var callable  */
    public $onCrudSave;

    /** @additionalComponentDependencyDeclaration */

    public function __construct($crudId, Model\CrudRepository $crudRepository /** @additionalComponentDependencyHint */)
    {
        parent::__construct();

        $this->crudId = $crudId;

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
        $form->onSuccess[] = [$this, 'processFormValues'];

        return $form;
    }

    public function processFormValues(Form $form, $values)
    {
        $crud = $this->crudRepository->save($values, $this->crudId);

        $this->onCrudSave($this, $crud);
    }
}

interface ICrudFormFactory
{
    /**
     * @param int $crudId
     * @return CrudForm
     */
    public function create($crudId);
}