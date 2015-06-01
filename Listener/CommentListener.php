<?php

namespace Mykees\CommentBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Mykees\CommentBundle\Interfaces\CommentableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Events;

class CommentListener{


    public $container;
    public $manager;
    public $entity;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function preRemove(LifecycleEventArgs $args)
    {
        $model = $args->getEntity();
        $this->manager = $this->container->get('mykees.comment.manager');

        if($model instanceof CommentableInterface)
        {
            $this->manager->preDeleteComment($model);
        }
    }


    public function getSubscribedEvents()
    {
        return [
            Events::preRemove
        ];
    }

}