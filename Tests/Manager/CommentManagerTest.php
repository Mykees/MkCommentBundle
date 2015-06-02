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
    protected $context;
    protected $session;
    protected $formType;
    protected $class;
    protected $fos_user_class;


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
        $this->context = $this->getMockBuilder('Symfony\Component\Security\Core\SecurityContextInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formType = $this->getMockBuilder('Mykees\CommentBundle\Form\Type\CommentType')
            ->disableOriginalConstructor()
            ->getMock();
        $this->class = static::$kernel->getContainer()->getParameter('mykees_comment.comment.class');
        $this->fos_user_class = static::$kernel->getContainer()->getParameter('fos_user.model.user.class');
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
        $manager = new CommentManager($this->em,$this->form,$this->router,$this->context,$this->session,$this->formType,$this->class,$this->fos_user_class);
        $count = count($manager->findAllComments(['model'=>'Post','modelId'=>1]));
        $this->assertEquals(2, $count);
    }

    public function testFindAllComments()
    {
        $manager = new CommentManager($this->em,$this->form,$this->router,$this->context,$this->session,$this->formType,$this->class,$this->fos_user_class);
        $count = count($manager->findAllComments());
        $this->assertEquals(5, $count);
    }

    public function testRemoveComment()
    {
        $manager = new CommentManager($this->em,$this->form,$this->router,$this->context,$this->session,$this->formType,$this->class,$this->fos_user_class);
        $manager->deleteComment('Post',38);
        $count = count($manager->findAllComments());
        $this->assertEquals(4, $count);
    }
}