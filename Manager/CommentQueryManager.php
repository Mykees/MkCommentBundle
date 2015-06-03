<?php
namespace Mykees\CommentBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Mykees\CommentBundle\Interfaces\CommentableInterface;

class CommentQueryManager extends Manager{

	private $em;
	private $user;
	private $repository;
	private $comment_class;

	public function __construct(ManagerRegistry $managerRegistry, SecurityContextInterface $context, $class, $fos_user_class)
	{
        $this->em       = $managerRegistry->getManager();
        $security_token = $context->getToken();
        $this->comment_class = $class;
        $this->user = method_exists($security_token,'getUser') ? $security_token->getUser() : array();
        $this->repository = $managerRegistry->getRepository($this->comment_class);
        $this->user = $fos_user_class !== null ? $fos_user_class : null;
	}


    /**
     * Retrieve all comments for an entity
     * @param CommentableInterface $referer
     * @return mixed
     */
    public function findComments(CommentableInterface $referer)
    {
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
            ->setParameter('ref_id',$referer->getId())
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

}
