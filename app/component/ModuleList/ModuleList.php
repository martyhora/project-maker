<?php

namespace App\Component;

use App\Model,
    Nette;

class ModuleList extends \Nette\Application\UI\Control
{   
    /** @var Model\ModuleRepository */
    protected $moduleRepository;

    /** @var Model\ProjectRepository */
    protected $projectRepository;

    public function __construct(Model\ModuleRepository $moduleRepository , Model\ProjectRepository $projectRepository)
    {
        parent::__construct();

        $this->moduleRepository = $moduleRepository;

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

        $grid->addColumn('name', 'Název modulu')->enableSort();

        $grid->addColumn('title', 'Titulek modulu')->enableSort();

        $grid->addColumn('project_id', 'Projekt')->enableSort();

        $grid->addColumn('date_created', 'Datum vytvoření')->enableSort();

        $grid->setRowPrimaryKey('id');

        $reflection = new \ReflectionClass(get_class($this));

        $grid->addCellsTemplate(__DIR__ . '/' . $reflection->getShortName() . '_grid.latte');

        $grid->setDatasourceCallback(function($filter, $order) {
             return $this->moduleRepository->findRows($filter, $order);
        });

        $grid->setFilterFormFactory(function() {
        $form = new Nette\Forms\Container;

        $form->addText('name', 'Název modulu')->setAttribute('class', 'form-control');

        $form->addText('title', 'Titulek modulu')->setAttribute('class', 'form-control');

        $options = $this->projectRepository->findAll()->order('title')->fetchPairs('id', 'title');

        $form->addSelect('project_id', 'Projekt', $options)->setPrompt('- Vyberte -')->setAttribute('class', 'form-control');

        $form->addText('date_created', 'Datum vytvoření')->setAttribute('class', 'form-control');
        $form->addSubmit('filter', 'Filtrovat')->getControlPrototype()->class = 'btn btn-primary btn-flat';
        $form->addSubmit('cancel', 'Zrušit')->getControlPrototype()->class = 'btn btn-primary btn-flat';

        return $form;
    });

        return $grid;        
    }
}

interface IModuleListFactory
{
    /**
     * @return ModuleList
     */
    public function create();
}