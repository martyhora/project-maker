<?php

namespace App\Presenters;

class HomepagePresenter extends BasePresenter
{
    protected function startup()
    {
        parent::startup();

        if (!$this->user->isLoggedIn()) {
            $this->redirect('Auth:login');
        }
    }       
}
