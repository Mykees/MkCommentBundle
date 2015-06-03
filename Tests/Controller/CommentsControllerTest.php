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

   public function setUp()
   {


       $this->client = static::createClient();
       $this->container = $this->client->getContainer();
       $this->manager = $this->container->get('mykees.comment.manager');
       $this->managerQuery = $this->container->get('mykees.comment.query.manager');
       $this->em = static::$kernel->getContainer()
           ->get('doctrine')
           ->getManager()
       ;

       $fixtures = [
           'Mykees\CommentBundle\DataFixtures\ORM\LoadCommentData',
           'Mvc\BlogBundle\DataFixtures\ORM\LoadPostsData',
       ];
       $this->loadFixtures($fixtures);
       parent::setUp();
   }

    public function testCountCommentHtmlList()
    {
        $crawler = $this->client->request('GET', '/blog/title-1-1');
        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());
        $this->assertEquals(2,$crawler->filter('.comment__list')->count());
    }


    public function testRemoveAssociateComment()
    {
      $crawler = $this->client->request('GET', '/admin/delete/5');
      $this->assertEquals(302,$this->client->getResponse()->getStatusCode());

      $count = count($this->managerQuery->findAllComments());

      $this->assertEquals(4, $count);
    }

    public function testAddComment()
    {
          
        $crawler = $this->client->request('GET','/blog/title-1-9');
        $form = $crawler->selectButton('Submit')->form([
            'mykees_commentbundle_comment[username]'=>'Mykees',
            'mykees_commentbundle_comment[email]'=>'contact@mykees.fr',
            'mykees_commentbundle_comment[content]'=>'Salut les guedins',
            'mykees_commentbundle_comment[parentId]'=>0,
          ]);

        $this->client->submit($form);

        $this->assertEquals(302,$this->client->getResponse()->getStatusCode());

        $count = count($this->managerQuery->findAllComments());
        $this->assertEquals(6, $count);
    }

    public function testAddCommentWithEmptyName()
    {
          
        $crawler = $this->client->request('GET','/blog/title-1-13');
        $form = $crawler->selectButton('Submit')->form([
            'mykees_commentbundle_comment[username]'=>'',
            'mykees_commentbundle_comment[email]'=>'contact@mykees.fr',
            'mykees_commentbundle_comment[content]'=>'Salut les guedins',
            'mykees_commentbundle_comment[parentId]'=>0,
          ]);

        $this->client->submit($form);

        $count = count($this->managerQuery->findAllComments());
        $this->assertEquals(5, $count);
    }

    public function testAddCommentWithWrongEmailFormat()
    {
          
        $crawler = $this->client->request('GET','/blog/title-1-17');
        $form = $crawler->selectButton('Submit')->form([
            'mykees_commentbundle_comment[username]'=>'Mykees',
            'mykees_commentbundle_comment[email]'=>'contact.fr',
            'mykees_commentbundle_comment[content]'=>'Salut les guedins',
            'mykees_commentbundle_comment[parentId]'=>0,
          ]);

        $this->client->submit($form);

        $count = count($this->managerQuery->findAllComments());
        $this->assertEquals(5, $count);
    }


}
