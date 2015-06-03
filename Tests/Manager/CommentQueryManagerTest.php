<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 23/04/2015
 * Time: 13:56
 */

namespace Mykees\CommentBundle\Tests\Manager;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Mykees\CommentBundle\Manager\CommentQueryManager;

class CommentQueryManagerTest extends WebTestCase{

    protected $client;
    protected $container;
    protected $em;
    protected $registry;
    protected $comment_class;


    public function setUp()
    {
        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->registry =  $this->container->get('doctrine');
        $this->comment_class = $this->container->getParameter('mykees_comment.comment.class');

        $fixtures = [
            'Mykees\CommentBundle\DataFixtures\ORM\LoadCommentData',
            'Mvc\BlogBundle\DataFixtures\ORM\LoadPostsData',
        ];
        $this->loadFixtures($fixtures);

        parent::setUp();
    }


    public function testFindCommentsByCriteria()
    {
        $manager = new CommentQueryManager($this->registry,$this->comment_class);
        $count = count($manager->findAllComments(['model'=>'Post','modelId'=>1]));
        $this->assertEquals(2, $count);
    }

    public function testFindAllComments()
    {
        $manager = new CommentQueryManager($this->registry,$this->comment_class);
        $count = count($manager->findAllComments());
        $this->assertEquals(5, $count);
    }

    public function testRemoveComment()
    {
        $manager = new CommentQueryManager($this->registry,$this->comment_class);
        $manager->deleteComment('Post',38);
        $count = count($manager->findAllComments());
        $this->assertEquals(4, $count);
    }
}

