<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 15:27
 */

namespace Mykees\CommentBundle\Manager;


use Mykees\CommentBundle\Interfaces\CommentableInterface;
use Mykees\CommentBundle\Libs\Akismet;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Mykees\CommentBundle\Form\Type\CommentType;

class CommentManager extends Manager{

    protected $em;
    protected $formFactory;
    protected $router;
    protected $comment_class;
    protected $repository;
    protected $session;


    public function __construct(FormFactory $formFactory, Router $router, Session $session, CommentType $formType, $class)
    {
        $this->formFactory  = $formFactory;
        $this->router       = $router;
        $this->comment_class = $class;
        $this->formType = $formType;
        $this->session = $session;
    }

    /**
     * @param CommentableInterface $referer
     * @param null $success_message
     * @return object|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function createForm(CommentableInterface $referer, $success_message=null)
    {
        $dataForm = $this->session->get('form_comment_data');
        $refName  = $this->getClassShortName ($referer);
        $bundle   = $this->getBundleShortName($referer);
        $route    = $this->router->generate('mykees_comment_manage', ['bundle'=>$bundle,'ref'=>$refName,'ref_id'=>$referer->getId()]);

        if($this->session->has('success_message'))
        {
            $this->session->remove('success_message');
        }
        if($success_message)
        {
            $this->session->set('success_message',$success_message);
        }

        $form = $this->formFactory->create(
            $this->formType,
            new $this->comment_class(),
            ['action'=> $route,'method'=> 'POST']
        );

        if( !empty($dataForm) ){
            $form->bind($dataForm);
            $this->session->remove('form_comment_data');
        }

        return $form;
    }


    /**
     * Verify if the comment is a spam
     * @param $comment
     * @param Request $request
     * @param $akismetInit
     * @return bool
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    public function isSpam($comment, Request $request, $akismetInit)
    {
        if($akismetInit){
            $akismet     = new Akismet($akismetInit['website'],$akismetInit['api_key'],$request);

            $akismet->setCommentAuthor($comment->getUsername());
            $akismet->setCommentAuthorEmail($comment->getEmail());
            $akismet->setCommentContent($comment->getContent());
            $akismet->setUserIP($comment->getIp());

            return $akismet->isCommentSpam($request);
        }
        return false;
    }
}
