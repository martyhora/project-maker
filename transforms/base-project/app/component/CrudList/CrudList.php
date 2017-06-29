<?php

namespace App\Component;

use App\Model;
use Nette;

class CrudList extends \Nette\Application\UI\Control
{   
    /** @var Model\Crud */
    protected $crudRepository;

    /** @additionalComponentDependencyDeclaration */

    public function __construct(Model\Crud $crudRepository /** @additionalComponentDependencyHint */)
    {
        $this->crudRepository = $crudRepository;

        /** @additionalComponentDependencyAssertion */   
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

        /** @listFields */

        $grid->setRowPrimaryKey('id');

        $reflection = new \ReflectionClass(get_class($this));

        $grid->addCellsTemplate(__DIR__ . '/' . $reflection->getShortName() . '_grid.latte');

        $grid->setDatasourceCallback(function($filter, $order) {
             return $this->crudRepository->findRows($filter, $order);
        });

        $grid->setFilterFormFactory(function() {
            $form = new Nette\Forms\Container;
            
            /** @filterFormFields */

            $form->addSubmit('filter', 'Filtrovat')->getControlPrototype()->class = 'btn btn-primary btn-flat';
            $form->addSubmit('cancel', 'Zrušit')->getControlPrototype()->class = 'btn btn-primary btn-flat';

            return $form;
        });

        return $grid;        
    }
}