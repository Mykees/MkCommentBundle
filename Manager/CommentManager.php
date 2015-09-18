<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 03/09/2015
 * Time: 11:29
 */

namespace Mykees\CommentBundle\Manager;

use Mykees\CommentBundle\Interfaces\IsCommentable;
use Mykees\CommentBundle\Libs\Akismet;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Mykees\CommentBundle\Form\Type\CommentFormType;

class CommentManager extends Manager{

	protected $formFactory;
	protected $router;
	protected $comment_class;
	protected $repository;
	protected $session;
	protected $formType;

	public function __construct(FormFactory $formFactory, Router $router, Session $session, CommentFormType $formType, $class)
	{
		$this->formFactory  = $formFactory;
		$this->router       = $router;
		$this->comment_class = $class;
		$this->formType = $formType;
		$this->session = $session;
	}


	/**
	 * Create Comment Form
	 * @param IsCommentable $model
	 * @param array $labels
	 * @param null $success_message
	 * @return \Symfony\Component\Form\Form
	 */
	public function createForm(IsCommentable $model, $labels = [], $success_message=null)
	{
		$dataForm = $this->session->get('form_comment_data');
		$model_name  = $this->getClassShortName($model);
		$route    = $this->router->generate('mykees_comment_manage');
		$comment  = new $this->comment_class;
		$comment->setModel($model_name);
		$comment->setModelId($model->getId());

		$this->formType->username = !empty($labels['username']) ? $labels['username'] : false;
		$this->formType->email    = !empty($labels['email']) ? $labels['email'] : false;
		$this->formType->content  = !empty($labels['content']) ? $labels['content'] : false;


		if($this->session->has('success_message')) { $this->session->remove('success_message'); }
		if($success_message) { $this->session->set('success_message',$success_message); }

		$form = $this->formFactory->create(
			$this->formType,
			$comment,
			['action'=> $route,'method'=> 'POST']
		);

		if( !empty($dataForm) ){
			$form->submit($dataForm);
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
