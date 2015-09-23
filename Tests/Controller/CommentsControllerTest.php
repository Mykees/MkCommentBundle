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
	protected $query_manager;
    protected $em;
    protected $commentClass;
    protected $request;

   public function setUp()
   {


       $this->client = static::createClient();
       $this->container = $this->client->getContainer();
	   $this->manager = $this->container->get('mykees.comment.manager');
	   $this->query_manager = $this->container->get('mykees.comment.query.manager');
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
        $crawler = $this->client->request('GET', '/blog/title-1');
        $this->assertEquals(200,$this->client->getResponse()->getStatusCode());
        $this->assertEquals(3,$crawler->filter('.comment-list')->count());
    }

	public function testRemoveAssociateComment()
	{
		$this->client->request('GET', '/admin/delete/5');
		$this->assertEquals(302,$this->client->getResponse()->getStatusCode());

		$count = count($this->query_manager->findAllComments());

		$this->assertEquals(4, $count);
	}

	public function testAddComment()
	{
		$crawler = $this->client->request('GET','/blog/title-1');
		$this->assertEquals('Mvc\BlogBundle\Controller\BlogController::showAction',  $this->client->getRequest()->attributes->get('_controller'));
		$form = $crawler->selectButton('Poster')->form([
			'mykees_comment[username]'=>'Mykees',
			'mykees_comment[email]'=>'contact@mykees.fr',
			'mykees_comment[content]'=>'Salut les guedins',
			'mykees_comment[parentId]'=>0,
			'mykees_comment[model]'=>'Post',
			'mykees_comment[modelId]'=>1,
		]);

		$this->client->submit($form);

		$this->assertEquals(302,$this->client->getResponse()->getStatusCode());

		$count = count($this->query_manager->findAllComments());

		$this->assertEquals(6, $count);
	}

	public function testAddCommentWithEmptyName()
	{
		$crawler = $this->client->request('GET','/blog/title-1');
		$this->assertEquals('Mvc\BlogBundle\Controller\BlogController::showAction',  $this->client->getRequest()->attributes->get('_controller'));
		$form = $crawler->selectButton('Poster')->form([
			'mykees_comment[username]'=>'',
			'mykees_comment[email]'=>'contact@mykees.fr',
			'mykees_comment[content]'=>'Salut les guedins',
			'mykees_comment[parentId]'=>0,
			'mykees_comment[model]'=>'Post',
			'mykees_comment[modelId]'=>1,
		]);

		$this->client->submit($form);

		$count = count($this->query_manager->findAllComments());

		$this->assertEquals(5, $count);
	}

	public function testAddCommentWithWrongEmailFormat()
	{

		$crawler = $this->client->request('GET','/blog/title-1');
		$form = $crawler->selectButton('Poster')->form([
			'mykees_comment[username]'=>'Mykees',
			'mykees_comment[email]'=>'contact.fr',
			'mykees_comment[content]'=>'Salut les guedins',
			'mykees_comment[parentId]'=>0,
			'mykees_comment[model]'=>'Post',
			'mykees_comment[modelId]'=>1,
		]);

		$this->client->submit($form);

		$count = count($this->query_manager->findAllComments());

		$this->assertEquals(5, $count);
	}


	public function testAddCommmentWithAChild()
	{
		$crawler = $this->client->request('GET','/blog/title-2');

		$form = $crawler->selectButton('Poster')->form([
			'mykees_comment[username]'=>'Mykees',
			'mykees_comment[email]'=>'contact@mykees.fr',
			'mykees_comment[content]'=>'Salut les guedins',
			'mykees_comment[parentId]'=>0,
			'mykees_comment[model]'=>'Post',
			'mykees_comment[modelId]'=>1,
		]);
		$this->client->submit($form);

		$this->assertEquals(302,$this->client->getResponse()->getStatusCode());

		$count = count($this->query_manager->findAllComments());

		$this->assertEquals(6, $count);

		$form = $crawler->selectButton('Poster')->form([
			'mykees_comment[username]'=>'Marion',
			'mykees_comment[email]'=>'contact@marion.fr',
			'mykees_comment[content]'=>'Salut les guedins',
			'mykees_comment[parentId]'=>27,
			'mykees_comment[model]'=>'Post',
			'mykees_comment[modelId]'=>1,
		]);
		$this->client->submit($form);

		$this->assertEquals(302,$this->client->getResponse()->getStatusCode());

		$count = count($this->query_manager->findAllComments());
		$this->assertEquals(7, $count);


		$count = count($this->query_manager->findAllComments(['parentId'=>27]));
		$this->assertEquals(1, $count);
	}


	public function testPreDeleteComment()
	{
		$crawler = $this->client->request('GET','/blog/title-1');
		$form = $crawler->selectButton('Poster')->form([
			'mykees_comment[username]'=>'Mykees',
			'mykees_comment[email]'=>'contact@mykees.fr',
			'mykees_comment[content]'=>'Salut les guedins',
			'mykees_comment[parentId]'=>0,
			'mykees_comment[model]'=>'Post',
			'mykees_comment[modelId]'=>25,
		]);
		$this->client->submit($form);
		$this->assertEquals(302,$this->client->getResponse()->getStatusCode());
		$count = count($this->query_manager->findAllComments());
		$this->assertEquals(6, $count);


		$post = $this->em->getRepository('MvcBlogBundle:Post')->find(25);
		$this->em->remove($post);
		$this->em->flush();

		$count = count($this->query_manager->findAllComments());
		$this->assertEquals(5, $count);
	}


	public function testWithCommentDepthToZero(){
		$crawler = $this->client->request('GET', '/blog/title-1');
		$this->assertEquals(200,$this->client->getResponse()->getStatusCode());
		$this->assertEquals(0,$crawler->filter('.reply')->count());
	}

}