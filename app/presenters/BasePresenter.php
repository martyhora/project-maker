<?php

namespace App\Presenters;

use Nette;

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function beforeRender()
    {
        parent::beforeRender();
    }
}
