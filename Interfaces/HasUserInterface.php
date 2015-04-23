<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 21:17
 */

namespace Mykees\CommentBundle\Interfaces;


use FOS\UserBundle\Model\UserInterface;

interface HasUserInterface {

    public function getUser();

    public function setUser(UserInterface $user);
}