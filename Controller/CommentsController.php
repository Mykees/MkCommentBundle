<?php

namespace Mykees\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentsController extends Controller
{
	private function gEm()
	{
		return $this->getDoctrine()->getManager();
	}

	/**
	 * Manage comment process
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 * @internal param $bundle
	 * @internal param $ref
	 * @internal param $ref_id
	 */
	public function manageAction(Request $request)
	{
		$params = [
			'manager'=>$this->get('mykees.comment.manager'),
			'session'=>$request->getSession(),
			'ref'    => $request->request->get('model') ? $request->request->get('model') : $request->request->get('mykees_comment')['model'],
			'ref_id' => $request->request->get('modelId') ? $request->request->get('modelId') : $request->request->get('mykees_comment')['modelId']
		];

		$comment_class_name = $this->container->getParameter('mykees_comment.comment.class');
		$comment_class = new $comment_class_name();
		$form = $this->createForm($this->get('mykees.comment.form'),$comment_class);

		if('POST' == $request->getMethod()) {

			if ($form->handleRequest($request)->isValid()) {

				if($request->isXmlHttpRequest()) {
					return $this->ajaxInfo($params,$request,$comment_class);
				}

				$this->postInfo($params, $request, $comment_class, $params['session']);

			}else{

				if($request->isXmlHttpRequest()) {
					return $this->returnErrorAjax($form);
				}else{
					$this->error($request, $params['session']);
				}
			}

		}

		return $this->redirect($request->headers->get('referer') . '#comments_area');
	}

	/**
	 * @param $params
	 * @param $request
	 * @param $comment_class
	 * @return Response
	 */
	private function ajaxInfo($params,$request,$comment_class)
	{

		$comment = $this->postInfo($params, $request, $comment_class, $params['session']);
		$comment_depth = $this->container->getParameter('comment.depth');

		// Si la profondeur du commentaire parent est inférieure ou égale à la profondeur définis
		if($request->request->get('depth') < $comment_depth && $comment_depth >= 1)
		{
			$comment->setDepthReached($request->request->get('depth') + 1);//On incremente la profondeur max
			$comment->setDepth($comment->getId()); //la profondeur de réponse est égale a l'id
			$max_depth = false;
		}else{
			$comment->setDepthReached($request->request->get('depth'));//On à atteint la profondeur max
			$comment->setDepth($comment->getParentId());//la profondeur de réponse est éagle au commentaire parent
			$max_depth = true;
		}

		$template = $this->renderResponse($comment,$request,$max_depth);
		$json = json_encode([
			'template'=>$template,
			"parent_id"=>$comment->getParentId(),
			'comment_id'=>$comment->getId(),
			'max_depth'=>$max_depth,
			'success_message'=>$this->get('session')->getFlashBag()->get('comment_success')
		]);

		return new Response($json);
	}


	private function renderResponse($comment,$request,$max_depth)
	{
		if($comment->getParentId() > 0)
		{
			if( ($request->request->get('response_type') === "true" && $max_depth == true) ||
				($request->request->get('response_type') === "false" && $max_depth == true) ||
				($request->request->get('response_type') === "true" && $max_depth == false)
			){
				return $this->renderView('MykeesCommentBundle:Comments:unwrap_replies.html.twig',['comment'=>$comment,'recent_reply'=>true]);
			}else{
				return $this->renderView('MykeesCommentBundle:Comments:replies.html.twig',['comment'=>$comment,'recent_reply'=>true]);
			}
		}else{
			return $this->renderView('MykeesCommentBundle:Comments:comment.html.twig',['comment'=>$comment,'recent_reply'=>true]);
		}
	}


	/**
	 * Init/save comment and user info
	 * @param $request
	 * @param $comment
	 * @param $params
	 * @param $session
	 * @return
	 */
	private function postInfo($params, $request, $comment, $session)
	{
		$this->userInfo($request, $comment);
		$this->commentInfo($params,$comment,$request,$params['ref_id']);

		$this->save($comment);
		$this->messageFlash($session);

		return $comment;
	}

	/**
	 * Save comment entity
	 * @param $comment
	 */
	private function save($comment)
	{
		$this->gEm()->persist($comment);
		$this->gEm()->flush();

		return $comment;
	}

	/**
	 * Init comment info
	 * @param $params
	 * @param $comment
	 * @param $request
	 * @param $ref_id
	 * @return mixed
	 */
	private function commentInfo($params,$comment,$request,$ref_id)
	{
		$comment->setIp($request->getClientIp());
		$comment->setModel($params['ref']);
		$comment->setModelId($ref_id);
		//Spam ?
		$akismet = $this->container->hasParameter('akismet') ? $this->container->getParameter('akismet') : null;
		$params['manager']->isSpam($comment,$request,$akismet) ? $comment->setSpam(1) : $comment->setSpam(0);

		return $comment;
	}

	/**
	 * Init user info
	 * @param $request
	 * @param $comment
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
	private function userInfo($request, $comment)
	{
		$is_join = method_exists($comment,'getUser') ? true : false;

		if($this->getUser() !== null)
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

		return true;
	}

	/**
	 * Init message flash
	 * @param $session
	 * @return bool
	 */
	private function messageFlash($session)
	{
		$html = $session->has('success_message') ? $session->get('success_message') : "<strong>Merci!</strong> Votre message à bien été posté.";
		if($this->get('session')->getFlashBag()->has('comment_success'))
		{
			$this->get('session')->getFlashBag()->set('comment_success','');
		}
		$this->get('session')->getFlashBag()->add('comment_success',$html);

		return true;
	}

	/**
	 * @param $request
	 * @param $session
	 */
	private function error($request, $session)
	{
		$requestData = $request->request->get('mykees_comment');
		if(!empty($requestData))
		{
			$session->set('form_comment_data',$request->request->get('mykees_comment'));
		}
	}

	/**
	 * Get errors form for ajax
	 * @param $form
	 * @return array
	 */
	protected function getErrorsAsArray($form)
	{
		$errors = array();
		foreach ($form->getErrors() as $error)
			$errors[] = $error->getMessage();

		foreach ($form->all() as $key => $child) {
			if ($err = $this->getErrorsAsArray($child))
				$errors[$key] = $err;
		}
		return $errors;
	}

	public function returnAjaxErrors($form)
	{
		$json = json_encode(['error'=>'error','error_fields'=>$this->getErrorsAsArray($form)]);
		return new Response($json);
	}
}
