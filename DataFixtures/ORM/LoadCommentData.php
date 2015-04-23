<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 23/04/2015
 * Time: 02:08
 */

namespace Mykees\CommentBundle\DataFixtures\ORM;


use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadCommentData implements FixtureInterface,ContainerAwareInterface {

    public $container;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $comment_class = $this->container->getParameter('mykees_comment.comment.class');

        for($i=1; $i<=3 ;$i++)
        {
            $comment = new $comment_class();
            $comment->setUsername('admin'.$i);
            $comment->setEmail("admin-{$i}@gmail.com");
            $comment->setContent('contenu n-'.$i);
            $comment->setCreatedAt(new \DateTime());
            $comment->setModel('Post');
            if($i <= 2)
            {
                $comment->setModelId($i);
            }else{
                $comment->setModelId(1);
            }
            $comment->setParentId(0);
            $comment->setSpam(0);
            $comment->setIp("192.168.56.1");

            $manager->persist($comment);
            $manager->flush();
        }
    }
}