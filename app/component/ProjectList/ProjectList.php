<?php

namespace App\Component;

use App\Model,
    Nette;
use Nette\Application\UI\Form;

class ProjectList extends \Nette\Application\UI\Control
{   
    /** @var Model\ProjectRepository */
    protected $projectRepository;

    public function __construct(Model\ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function render()
    {
        $reflection = new \ReflectionClass(get_class($this));

        $this->template->setFile(__DIR__ . '/' . $reflection->getShortName() . '.latte');

        $this->template->render();
    }

    public function createComponentGrid()
    {
        $grid = new \Nextras\Datagrid\Datagrid;

        $grid->addColumn('title', 'Název projektu')->enableSort();

        $grid->addColumn('transformation', 'Transformace')->enableSort();

        $grid->addColumn('date_created', 'Datum vytvoření')->enableSort();

        $grid->setRowPrimaryKey('id');

        $reflection = new \ReflectionClass(get_class($this));

        $grid->addCellsTemplate(__DIR__ . '/' . $reflection->getShortName() . '_grid.latte');

        $grid->setDatasourceCallback(function($filter, $order) {
             return $this->projectRepository->findRows($filter, $order);
        });

        $grid->setFilterFormFactory(function() {
        $form = new Nette\Forms\Container;
            
        $form->addText('title', 'Název projektu')->setAttribute('class', 'form-control');

        $form->addSelect('transformation', 'Transformace', Model\ModuleRepository::$transformations)
             ->setPrompt('- Vyberte -')->setAttribute('class', 'form-control');

        $form->addText('date_created', 'Datum vytvoření')->setAttribute('class', 'form-control');
            $form->addSubmit('filter', 'Filtrovat')->getControlPrototype()->class = 'btn btn-primary btn-flat';
            $form->addSubmit('cancel', 'Zrušit')->getControlPrototype()->class = 'btn btn-primary btn-flat';

            return $form;
        });

        return $grid;        
    }
}

interface IProjectListFactory
{
    /**
     * @return ProjectList
     */
    public function create();
}