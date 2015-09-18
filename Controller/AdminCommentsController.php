<?php

namespace Mykees\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class AdminCommentsController extends Controller
{
	private function gEm()
	{
		return $this->getDoctrine()->getManager();
	}

	public function deleteWithChildrenAction($model, $model_id, $comment_id, Request $request)
	{
		$manager = $this->get('mykees.comment.query.manager');
		$comments = $manager->findCommentsByModelAndId($model, $model_id,true);
		$children_ids = $manager->getChildren($comments[$comment_id]);

		array_push($children_ids,$comment_id);

		$manager->deleteByCommentIds($children_ids);

		return $this->redirect($request->headers->get('referer'));
	}


	public function deleteAction($id, Request $request)
	{
		$class = $this->container->getParameter('mykees_comment.comment.class');
		$explose_class = explode('\\',$class);
		$repository = $explose_class[0].$explose_class[1].':'.$explose_class[3];
		$comment = $this->gEm()->getRepository($repository)->find($id);

		$this->gEm()->remove($comment);
		$this->gEm()->flush();

		return $this->redirect($request->headers->get('referer'));
	}
}
