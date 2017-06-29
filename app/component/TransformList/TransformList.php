<?php

namespace App\Component;

use App\Model,
    Nette;

class TransformList extends \Nette\Application\UI\Control
{   
    /** @var Model\TransformRepository */
    protected $transformRepository;

    public function __construct(Model\TransformRepository $transformRepository)
    {
        $this->transformRepository = $transformRepository;
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

        $grid->addColumn('title', 'Název transformace')->enableSort();

        $grid->addColumn('date_created', 'Datum vytvoření')->enableSort();

        $grid->setRowPrimaryKey('id');

        $reflection = new \ReflectionClass(get_class($this));

        $grid->addCellsTemplate(__DIR__ . '/' . $reflection->getShortName() . '_grid.latte');

        $grid->setDatasourceCallback(function($filter, $order) {
             return $this->transformRepository->findRows($filter, $order);
        });

        $grid->setFilterFormFactory(function() {
        $form = new Nette\Forms\Container;
            
        $form->addText('title', 'Název transformace')->setAttribute('class', 'form-control')->addRule(Form::FILLED, "Pole 'Název transformace' je povinné.");

        $form->addText('date_created', 'Datum vytvoření')->setAttribute('class', 'form-control');
            $form->addSubmit('filter', 'Filtrovat')->getControlPrototype()->class = 'btn btn-primary btn-flat';
            $form->addSubmit('cancel', 'Zrušit')->getControlPrototype()->class = 'btn btn-primary btn-flat';

            return $form;
        });

        return $grid;        
    }
}