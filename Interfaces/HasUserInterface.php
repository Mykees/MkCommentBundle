<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 03/09/2015
 * Time: 10:34
 */

namespace Mykees\CommentBundle\Interfaces;

use FOS\UserBundle\Model\UserInterface;

interface HasUserInterface {

	public function getUser();
	public function setUser(UserInterface $user);
}
