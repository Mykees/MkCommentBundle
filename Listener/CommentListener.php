<?php

namespace Mykees\CommentBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Mykees\CommentBundle\Interfaces\IsCommentable;
use Doctrine\ORM\Events;
use Mykees\CommentBundle\Manager\CommentQueryManager;
use Doctrine\Common\Persistence\ManagerRegistry;

class CommentListener{


	public $container;
	public $entity;
	public $fos_user;
	public $class;
	public $managerRegistry;
	public $depth;


	public function __construct(ManagerRegistry $managerRegistry, $class, $fos_user_class, $depth)
	{
		$this->managerRegistry = $managerRegistry;
		$this->class = $class;
		$this->fos_user  = $fos_user_class;
		$this->depth = $depth;
	}


	public function preRemove(LifecycleEventArgs $args)
	{
		$model = $args->getEntity();

		if($model instanceof IsCommentable)
		{
			$manager = new CommentQueryManager($this->managerRegistry,$this->class,$this->depth);
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
