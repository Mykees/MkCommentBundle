<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 03/09/2015
 * Time: 11:29
 */

namespace Mykees\CommentBundle\Manager;

use Doctrine\Common\Persistence\ManagerRegistry;
use Mykees\CommentBundle\Interfaces\IsCommentable;

class CommentQueryManager extends Manager{

	private $em;
	private $repository;
	private $comment_class;
	private $depth;
	private $depth_reached;

	public function __construct(ManagerRegistry $managerRegistry, $class, $depth)
	{
		$this->em = $managerRegistry->getManager();
		$this->comment_class = $class;
		$this->repository = $managerRegistry->getRepository($this->comment_class);
		$this->depth = $depth;
	}


	/**
	 * Find one comment
	 * @param $id
	 * @param null $model_name
	 * @param int $model_id
	 * @return mixed
	 */
	public function findOneComment($id, $model_name=null,$model_id=0)
	{
		$is_join = method_exists($this->comment_class,'getUser') ? true : false;
		$qb = $this->repository->createQueryBuilder('c');

		return $this->oneCommentQueryResult($qb, $id, $model_name, $model_id, $is_join);
	}


	/**
	 * Find a comment by model referer
	 * @param $id
	 * @param IsCommentable $model
	 * @return mixed
	 */
	public function findOneCommentByReferer($id,IsCommentable $model)
	{
		$model_name  = $this->getClassShortName( $model );
		$model_id  = $model->getId();
		$qb = $this->repository->createQueryBuilder('c');
		$is_join = method_exists($this->comment_class,'getUser') ? true : false;

		return $this->oneCommentQueryResult($qb, $id, $model_name, $model_id, $is_join);
	}

	/**
	 * Find all comments for an entity
	 * @param IsCommentable $model
	 * @param bool $get_by_id
	 * @return mixed
	 */
	public function findComments(IsCommentable $model, $get_by_id=false)
	{
		$model_name  = $this->getClassShortName( $model );
		$comments = [];
		$is_join = method_exists($this->comment_class,'getUser') ? true : false;
		$qb = $this->repository->createQueryBuilder('c');

		$comments['comments'] = $this->commentQueryResult($qb, $model_name, $model->getId(), $is_join);
		$comments['count'] = count($comments['comments']);

		return $this->buildCommentStructure($comments,$get_by_id);
	}


	/**
	 * Find all comments for an entity
	 * @param $model_name
	 * @param $model_id
	 * @param bool $get_by_id
	 * @return mixed
	 */
	public function findCommentsByModelAndId($model_name, $model_id, $get_by_id=false)
	{
		$comments = [];
		$is_join = method_exists($this->comment_class,'getUser') ? true : false;
		$qb = $this->repository->createQueryBuilder('c');

		$comments['comments'] = $this->commentQueryResult($qb, $model_name, $model_id, $is_join);
		$comments['count'] = count($comments['comments']);

		return $this->buildCommentStructure($comments,$get_by_id);
	}


	/**
	 * Build Comment Structure
	 * @param $comments
	 * @param bool $get_by_id
	 * @return mixed
	 */
	private function buildCommentStructure($comments, $get_by_id=false)
	{
		$comments_by_id = [];
		return $this->build($comments,$comments_by_id,$get_by_id);
	}


	private function build($comments,$comments_by_id, $get_by_id)
	{
		foreach($comments['comments'] as $comment)
		{
			$comments_by_id[$comment->getId()] = $comment;
			$comment->setDepth($this->depth);
		}

		//Comment parent and children
		foreach($comments['comments'] as $k=>$comment)
		{
			if($this->depth > 0 && $comment->getParentId() > 0)
			{
				$parent_id = $this->depthComment($comments_by_id,$comment->getId());

				//Si la pronfondeur atteinte est plus petite que la profondeur definis
				//alors la réponse aura l'id du commentaire
				//Sinon
				//la réponse aura l'id du parent
				if($this->depth_reached < $this->depth)
				{
					$comment->setDepth($comment->getId());
				}else{
					$comment->setDepth($parent_id);
				}
				$comment->setDepthReached($this->depth_reached);
				$comments_by_id[$parent_id]->setChildren($comment);
				unset($comments['comments'][$k]);

			}else if($this->depth == 0){
				$comment->setDepth(0);
			}else{
				$comment->setDepth($comment->getId());
			}

		}

		if($get_by_id)
		{
			return $comments_by_id;
		}else{
			return $comments;
		}
	}

	/**
	 * config depth of comments
	 * @param $comments_by_id
	 * @param $comment_id
	 * @return mixed
	 */
	private function depthComment($comments_by_id,$comment_id)
	{
		$parent_id=[];
		$index = $comment_id;
		$parent = true;
		$depth_reached = 0;

		while($parent)
		{
			$comment = $comments_by_id[$index];
			if($comment->getParentId())
			{
				$parent_id[] = $comments_by_id[$index]->getParentId();
				$index = $comments_by_id[$index]->getParentId();
				$depth_reached++;
			}else{
				$parent = false;
			}
		}
		$this->depth_reached = $depth_reached;
		$array_reverse = array_reverse($parent_id);

		return $this->depth < count($array_reverse) ? $array_reverse[$this->depth-1] : end($array_reverse);
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
	 * @param $qb
	 * @param $model_name
	 * @param $model_id
	 * @param $is_join
	 * @return mixed
	 */
	private function commentQueryResult($qb, $model_name, $model_id, $is_join)
	{
		if($is_join)
		{
			$qb
				->leftJoin('c.user','u')
				->addSelect('u')
			;
		}

		return $qb
			->where("c.model = :ref")
			->setParameter('ref',$model_name)
			->andWhere("c.modelId = :ref_id")
			->setParameter('ref_id',$model_id)
			->orderBy('c.createdAt', 'DESC')
			->getQuery()
			->getResult()
		;
	}

	private function oneCommentQueryResult($qb, $id, $model_name, $model_id, $is_join)
	{
		if($is_join)
		{
			$qb
				->leftJoin('c.user','u')
				->addSelect('u')
			;
		}
		$qb->where('c.id = :id')
			->setParameter('id',$id)
		;

		if($model_name)
		{
			$qb->andWhere("c.model = :ref")
				->setParameter('ref',$model_name)
			;
		}
		if($model_id > 0)
		{
			$qb->andWhere("c.modelId = :ref_id")
				->setParameter('ref_id',$model_id)
			;
		}

		return $qb->getQuery()->getOneOrNullResult();
	}


	/**
	 * @param $ids
	 * @return mixed
	 */
	public function deleteByCommentIds($ids)
	{
		$comment_class = $this->classRepository($this->comment_class);

		return $this->repository
			->createQueryBuilder('c')
			->delete($comment_class,'c')
			->where("c.id IN(:ids)")
			->setParameter('ids', array_values($ids))
			->getQuery()
			->execute()
		;
	}


	/**
	 * Delete a comment by id
	 * @param $id
	 * @return mixed
	 */
	public function deleteComment($id)
	{
		$comment_class = $this->classRepository($this->comment_class);

		return $this->repository
			->createQueryBuilder('c')
			->delete($comment_class,'c')
			->where("c.id = id")
			->setParameter('id', $id)
			->getQuery()
			->execute()
		;
	}



	/**
	 * Remove ccomment children
	 * @param $comment
	 * @return array
	 */
	public function getChildren($comment)
	{
		$children_ids = [];
		foreach($comment->getChildren() as $child)
		{
			$children_ids[] = $child->getId();
			if($child->getChildren())
			{
				$children_ids = array_merge($children_ids, $this->getChildren($child));
			}
		}

		return $children_ids;
	}

	/**
	 * Pre delete comment
	 * @param IsCommentable $referer
	 * @return bool
	 */
	public function preDeleteComment(IsCommentable $referer)
	{
		$comments = $this->findComments($referer, true);
		foreach($comments as $k=>$comment)
		{
			$children_ids = $this->getChildren($comment);
			array_push($children_ids,$k);

			$this->deleteByCommentIds($children_ids);
		}
		return true;
	}
}