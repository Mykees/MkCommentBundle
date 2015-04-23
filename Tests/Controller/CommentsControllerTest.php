<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 23/04/2015
 * Time: 02:28
 */

namespace Mykees\CommentBundle\Tests\Controller;


use Liip\FunctionalTestBundle\Test\WebTestCase;

class CommentsControllerTest extends WebTestCase{

    protected $client;
    protected $container;
    protected $manager;
    protected $em;
    protected $commentClass;
    protected $request;

//    public function setUp()
//    {
//
//
//        $this->client = static::createClient();
//        $this->container = $this->client->getContainer();
//        $this->manager = $this->container->get('mykees.comment.manager');
//        $this->em = static::$kernel->getContainer()
//            ->get('doctrine')
//            ->getManager()
//        ;
//
//        $fixtures = [
//            'Mykees\CommentBundle\DataFixtures\ORM\LoadCommentData',
//        ];
//        $this->loadFixtures($fixtures);
//        parent::setUp();
//    }
//
//
//    public function testFindCommentsByCriteria()
//    {
//        $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
//        $count = count($this->manager->findAllComments(['model'=>'Post','modelId'=>1]));
//        $this->assertEquals(3, $count);
//    }
//    public function testRemoveComment()
//    {
//        $this->manager->deleteComment(4);
//        $count = count($this->manager->findAllComments(['model'=>'Post','modelId'=>1]));
//        $this->assertEquals(2, $count);
//    }

}