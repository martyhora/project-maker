<?php

namespace App\Presenters;

use Nette;

use Nette\Application\UI;

class AuthPresenter extends BasePresenter
{
	protected function createComponentSignInForm()
	{
        $form = new UI\Form;
		$form->addText('username', 'Username:')
		     ->setAttribute('class', 'form-control')
			 ->setRequired('Vyplňte prosím uživatelské jméno.');

		$form->addPassword('password', 'Password:')
		     ->setAttribute('class', 'form-control')
			 ->setRequired('Vyplňte prosím heslo.');

		$form->addSubmit('send', 'Přihlásit se')->setAttribute('style', 'width: 100%')->setAttribute('class', 'btn btn-primary btn-flat');

		$form->onSuccess[] = $this->signInFormSubmitted;
		return $form;
	}

	public function signInFormSubmitted($form)
	{
		$values = $form->getValues();

		try {
			$this->getUser()->login($values->username, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
			return;
		}

		$this->redirect('Homepage:');
	}

	public function actionLogout()
	{
		$this->getUser()->logout();
		$this->flashMessage('Odhlášení proběhlo úspěšně.');
		$this->redirect('login');
	}

}
