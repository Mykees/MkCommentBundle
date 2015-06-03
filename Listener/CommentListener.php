<?php

namespace Mykees\CommentBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Mykees\CommentBundle\Interfaces\CommentableInterface;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Mykees\CommentBundle\Manager\CommentQueryManager;
use Doctrine\Common\Persistence\ManagerRegistry;

class CommentListener{


    public $container;
    public $entity;
    public $fos_user;
    public $token;
    public $class;
    public $managerRegistry;


    public function __construct(ManagerRegistry $managerRegistry, SecurityContextInterface $security_token, $class, $fos_user_class)
    {
        $this->managerRegistry = $managerRegistry;
        $this->token = $security_token;
        $this->class = $class;
        $this->fos_user  = $fos_user_class;
    }


    public function preRemove(LifecycleEventArgs $args)
    {
        $model = $args->getEntity();

        if($model instanceof CommentableInterface)
        {
            $manager = new CommentQueryManager($this->managerRegistry,$this->token,$this->class,$this->fos_user);
            $manager->preDeleteComment($model);
        }
    }


    public function getSubscribedEvents()
    {
        return [
            Events::preRemove
        ];
    }

}
