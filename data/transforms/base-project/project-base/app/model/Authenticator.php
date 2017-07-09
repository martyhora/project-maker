<?php

namespace App\Model;

use Nette,
	Nette\Security,
	Nette\Security\Passwords;

class Authenticator extends Nette\Object implements Security\IAuthenticator
{
	/** @var UserRepository */
	private $userRepository;

	public function __construct(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}

	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;
		$row = $this->userRepository->findByName($username);

		if (!$row) {
			throw new Security\AuthenticationException('Nesprávné přihlašovací jméno.', self::IDENTITY_NOT_FOUND);
		}

		if (!Passwords::verify($password, $row->password)) {
			throw new Security\AuthenticationException('Nesprávné heslo.', self::INVALID_CREDENTIAL);
		}

		$rowArray = $row->toArray();

		unset($rowArray['password']);
		return new Security\Identity($row->id, NULL, $rowArray);
	}

	public function setPassword($id, $password)
	{
		$this->userRepository->findBy(['id' => $id])->update([
			'password' => Passwords::hash($password),
		]);
	}
}