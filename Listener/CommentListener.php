<?php

namespace Mykees\CommentBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Mykees\CommentBundle\Interfaces\CommentableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CommentListener{

    public $manager;
    public $entity;


    public function __construct(CommentManager $manager)
    {
        $this->manager = $manager;
    }


    public function preRemove(LifecycleEventArgs $args)
    {
        $model = $args->getEntity();

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