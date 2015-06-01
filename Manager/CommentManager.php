<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 15:27
 */

namespace Mykees\CommentBundle\Manager;


use Doctrine\ORM\EntityManager;
use Mykees\CommentBundle\Interfaces\CommentableInterface;
use Mykees\CommentBundle\Libs\Akismet;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class CommentManager extends Manager{

    protected $em;
    protected $formFactory;
    protected $router;
    protected $container;
    protected $user;
    protected $comment_class;
    protected $repository;


    public function __construct(EntityManager $entityManager, FormFactory $formFactory, Router $router, ContainerInterface $container)
    {
        $this->em           = $entityManager;
        $this->formFactory  = $formFactory;
        $this->router       = $router;
        $this->container    = $container;
        $this->comment_class = $this->container->getParameter('mykees_comment.comment.class');
        $security_token = $container->get('security.token_storage')->getToken();
        $this->user = method_exists($security_token,'getUser') ? $security_token->getUser() : array();
        $this->repository = $entityManager->getRepository($this->comment_class);
        $this->user = $this->container->hasParameter('fos_user.model.user.class') ? '\\'.$this->container->getParameter('fos_user.model.user.class') : null;
    }

    /**
     * @param CommentableInterface $referer
     * @param null $success_message
     * @return object|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    public function createForm(CommentableInterface $referer, $success_message=null)
    {
        $session  = $this->container->get('session');
        $dataForm = $session->get('form_comment_data');
        $getId    = $this->primaryKey($referer);
        $refName  = $this->getClassShortName ($referer);
        $bundle   = $this->getBundleShortName($referer);
        $form     = $this->container->get('mykees.comment.form');
        $route    = $this->router->generate('mykees_comment_manage', ['bundle'=>$bundle,'ref'=>$refName,'ref_id'=>$referer->$getId()]);

        if($session->has('success_message'))
        {
            $session->remove('success_message');
        }
        if($success_message)
        {
            $session->set('success_message',$success_message);
        }

        $form = $this->formFactory->create(
            $form,
            new $this->comment_class(),
            ['action'=> $route,'method'=> 'POST']
        );

        if( !empty($dataForm) ){
            $form->bind($dataForm);
            $session->remove('form_comment_data');
        }

        return $form;
    }

    /**
     * Retrieve all comments for an entity
     * @param CommentableInterface $referer
     * @return mixed
     */
    public function findComments(CommentableInterface $referer)
    {
        $getId    = $this->primaryKey( $referer );
        $refName  = $this->getClassShortName ( $referer );
        $comments = [];
        $is_join = method_exists($this->comment_class,'getUser') ? true : false;
        $qb = $this->repository->createQueryBuilder('c');

        if($is_join)
        {
            $qb
                ->leftJoin('c.user','u')
                ->addSelect('u')
            ;
        }

        $comments['comments'] = $qb
            ->where("c.model = :ref")
            ->setParameter('ref',$refName)
            ->andWhere("c.modelId = :ref_id")
            ->setParameter('ref_id',$referer->$getId())
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        $comments['count'] = count($comments['comments']);

        return $this->buildCommentStructure($comments);
    }


    /**
     * Retrieve all comments by model and model id
     * @param $model
     * @param $modelId
     * @return mixed
     */
    public function findByModelAndModelId($model, $modelId)
    {
        $is_join = method_exists($this->comment_class,'getUser') ? true : false;
        $qb = $this->repository->createQueryBuilder('c');

        if($is_join)
        {
            $qb
                ->leftJoin('c.user','u')
                ->addSelect('u')
            ;
        }

        $comments['comments'] = $qb
            ->where("c.model = :ref")
            ->setParameter('ref',$model)
            ->andWhere("c.modelId = :ref_id")
            ->setParameter('ref_id',$modelId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        if(count($comments['comments']))
        {
            $comments['count'] = count($comments['comments']);

            return $this->buildCommentStructure($comments);
        }else{
            return false;
        }
    }

    /**
     * Build comments and replies
     * @param $comments
     * @return mixed
     */
    public function buildCommentStructure($comments)
    {
        $replies  = [];

        foreach($comments['comments'] as $kk=>$comment)
        {
            if($comment->getParentId() > 0){
                $replies[$comment->getParentId()][] = $comment;
                unset($comments['comments'][$kk]);
            }
        }
        foreach ($comments['comments'] as $k => $comment) {
            if(!empty($replies[$comment->getId()])){
                $comments['comments'][$k]->replies = array_reverse($replies[$comment->getId()]);
            }else{
                $comments['comments'][$k]->replies = [];
            }
        }

        return $comments;
    }

    /**
     * Retrieve all comments
     * @param array $criteria
     * @param null $orderBy
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public function findAllComments(array $criteria=[],$orderBy=null,$limit=null,$offset=null)
    {
        if(!empty($criteria) || isset($orderBy) || isset($limit) || isset($offset))
        {
            return $this->repository->findBy($criteria,$orderBy,$limit,$offset);
        }else{
            return $this->repository->findAll();
        }
    }

    /**
     * Delete comment
     * @param $model
     * @param $comment_id
     * @return bool
     * @internal param $modelId
     */
    public function deleteComment($model,$comment_id)
    {
        $comments = $this->repository
            ->createQueryBuilder('c')
            ->where('c.model = :model')
            ->setParameter('model',$model)
            ->andWhere("c.id = :comment_id")
            ->setParameter('comment_id',$comment_id)
            ->orWhere("c.parentId = :parent_id")
            ->setParameter('parent_id',$comment_id)
            ->getQuery()
            ->getResult()
        ;

        foreach($comments as $comment)
        {
            $this->em->remove($comment);
        }
        $this->em->flush();

        return true;
    }

    /**
     * Pre delete comment
     * @param CommentableInterface $referer
     * @return bool
     */
    public function preDeleteComment(CommentableInterface $referer)
    {
        $comments = $this->findComments($referer);
        
        foreach($comments['comments'] as $comment)
        {
            if(count($comment->replies) >= 1)
            {
                foreach($comment->replies as $reply)
                {
                    $this->em->remove($reply);
                }
            }
            $this->em->remove($comment);
        }

        $this->em->flush();

        return true;
    }


    /**
     * Verify if the comment is a spam
     * @param $comment
     * @return bool
     * @throws \Mykees\CommentBundle\Libs\exception
     */
    public function isSpam($comment)
    {
        $akismetInit = $this->container->hasParameter('akismet') ? $this->container->getParameter('akismet') : null;

        if($akismetInit){
            $akismet     = new Akismet($akismetInit['website'],$akismetInit['api_key']);

            $akismet->setCommentAuthor($comment->getUsername());
            $akismet->setCommentAuthorEmail($comment->getEmail());
            $akismet->setCommentContent($comment->getContent());
            $akismet->setUserIP($comment->getIp());

            return $akismet->isCommentSpam();
        }
        return false;
    }

}