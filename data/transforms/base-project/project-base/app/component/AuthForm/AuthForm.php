<?php

namespace App\Component;

use Nette;
use Nette\Application\UI\Form;

class AuthForm extends \Nette\Application\UI\Control
{   
    /** @var Nette\Security\User */
    protected $user;

    /** @var callable  */
    public $onAuthSucccess;

    public function __construct(Nette\Security\User $user)
    {
        parent::__construct();

        $this->user = $user;
    }

    public function render()
    {
        $this->template->setFile(__DIR__ . '/AuthForm.latte');

        $this->template->render();
    }

    protected function createComponentForm()
    {
        $form = new Form;

        $form->addText('username', 'Username:')
            ->setAttribute('class', 'form-control')
            ->setRequired('Vyplňte prosím uživatelské jméno.');

        $form->addPassword('password', 'Password:')
            ->setAttribute('class', 'form-control')
            ->setRequired('Vyplňte prosím heslo.');

        $form->addSubmit('send', 'Přihlásit se')->setAttribute('style', 'width: 100%')->setAttribute('class', 'btn btn-primary btn-flat');

        $form->onSuccess[] = [$this, 'authFormSubmitted'];

        return $form;
    }

    public function authFormSubmitted(Form $form, $values)
    {
        try {
            $this->user->login($values->username, $values->password);

            $this->onAuthSucccess($this);
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError($e->getMessage());
        }
    }
}

interface IAuthFormFactory
{
    /**
     * @return AuthForm
     */
    public function create();
}