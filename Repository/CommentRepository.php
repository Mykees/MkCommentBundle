<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 04/09/2015
 * Time: 14:31
 */

namespace Mykees\CommentBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CommentRepository extends EntityRepository
{
	public function deleteByCommentIds(array $ids)
	{
		return $this->_em->createQueryBuilder()
			->delete("MykeesCommentBundle:Comment",'c')
			->where("c.id IN(:ids)")
			->setParameter('ids', array_values($ids))
			->getQuery()
			->execute()
		;
	}
}
