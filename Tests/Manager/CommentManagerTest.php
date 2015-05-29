<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 23/04/2015
 * Time: 13:56
 */

namespace Mykees\CommentBundle\Tests\Manager;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Mykees\CommentBundle\Manager\CommentManager;

class CommentManagerTest extends WebTestCase{

    protected $client;
    protected $container;
    protected $em;
    protected $form;
    protected $router;


    public function setUp()
    {
        $this->client = static::createClient();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
        ;
        $this->form = $this->getMockBuilder('Symfony\Component\Form\FormFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
        $this->container = $this->client->getContainer();

        $fixtures = [
            'Mykees\CommentBundle\DataFixtures\ORM\LoadCommentData',
            'Mvc\BlogBundle\DataFixtures\ORM\LoadPostsData',
        ];
        $this->loadFixtures($fixtures);
        parent::setUp();
    }


    public function testFindCommentsByCriteria()
    {
        $manager = new CommentManager($this->em,$this->form,$this->router,$this->container);
        $count = count($manager->findAllComments(['model'=>'Post','modelId'=>1]));
        $this->assertEquals(2, $count);
    }

    public function testFindAllComments()
    {
        $manager = new CommentManager($this->em,$this->form,$this->router,$this->container);
        $count = count($manager->findAllComments());
        $this->assertEquals(5, $count);
    }

    public function testRemoveComment()
    {
        $manager = new CommentManager($this->em,$this->form,$this->router,$this->container);
        $manager->deleteComment('Post',38);
        $count = count($manager->findAllComments());
        $this->assertEquals(4, $count);
    }
}