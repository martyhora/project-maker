<?php

namespace App\Presenters;

use App\Component\AuthForm;
use App\Component\IAuthFormFactory;

class AuthPresenter extends BasePresenter
{
    /** @var IAuthFormFactory @inject */
    public $authFormFactory;

	protected function createComponentAuthForm()
	{
        $component = $this->authFormFactory->create();

        $component->onAuthSucccess[] = function(AuthForm $form) {
            $this->redirect('Project:');
        };

        return $component;
	}

	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->flashMessage('Odhlášení proběhlo úspěšně.');
		$this->redirect('login');
	}
}
