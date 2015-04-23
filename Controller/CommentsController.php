<?php

namespace Mykees\CommentBundle\Controller;

use Mykees\CommentBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CommentsController extends Controller
{

    public function gEm()
    {
        return $this->getDoctrine()->getManager();
    }

    public function manageAction($bundle, $ref, $ref_id, Request $request)
    {
        $manager = $this->get('mykees.comment.manager');
        $session = $request->getSession();
        $comment_class = $this->container->getParameter('mykees_comment.comment.class');
        $comment = new $comment_class();
        $entity  = $this->gEm()->getRepository("$bundle:$ref")->find($ref_id);
        $form = $this->createForm(new CommentType($comment,$this->container),$comment);
        $is_join = method_exists($comment,'getUser') ? true : false;

        if('POST' == $request->getMethod())
        {
            if($form->handleRequest($request)->isValid())
            {
                if($this->getUser() != null)
                {
                    if($this->getUser()->getUsername() != $comment->getUsername() || $this->getUser()->getEmail() != $comment->getEmail())
                    {
                        return $this->redirect($request->headers->get('referer') . '#comments_area');
                    }
                    if($is_join)
                    {
                        $comment->setUser($this->getUser());
                    }
                }
                $comment->setIp($request->getClientIp());
                $comment->setModel($manager->getClassShortName($entity));
                $comment->setModelId($ref_id);

                //Spam ?
                $manager->isSpam($comment) ? $comment->setSpam(1) : $comment->setSpam(0);

                $this->gEm()->persist($comment);
                $this->gEm()->flush();

                $html = $session->has('success_message') ? $session->get('success_message') : "<strong>Merci!</strong> Votre message à bien été posté.";
                $this->get('session')->getFlashBag()->add('comment_success',$html);
            }else{
                $requestData = $request->request->get('mykees_commentbundle_comment');
                if(!empty($requestData)){
                    $session->set('form_comment_data',$request->request->get('mykees_commentbundle_comment'));
                }
            }
        }

        return $this->redirect($request->headers->get('referer') . '#comments_area');
    }
}
