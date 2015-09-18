<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 03/09/2015
 * Time: 12:18
 */

namespace Mykees\CommentBundle\Twig\Extension;


use Symfony\Component\Validator\Constraints\DateTime;

class CommentExtension extends \Twig_Extension{

	/**
	 * Returns a list of functions to add to the existing list.
	 *
	 * @return array An array of functions
	 */
	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('helper_comment_form', [$this, 'commentForm'], [
				'is_safe'=>array('html'),
				'needs_environment'=>true
			]),
			new \Twig_SimpleFunction('helper_comments_list', [$this, 'commentsList'], [
				'is_safe'=>['html'],
				'needs_environment'=>true
			]),
			new \Twig_SimpleFunction('date_interval', [$this, 'dateInterval'], [
				'is_safe'=>array('html')
			]),
		);
	}

	public function getFilters() {
		return array(
			'url_decode' => new \Twig_Filter_Method($this, 'urlDecode')
		);
	}


	public function commentForm(\Twig_Environment $env, $form, $ajax=false, $options=[])
	{
		return $env->render('MykeesCommentBundle:Comments:form.html.twig',[
			'form'=>$form,
			'ajax'=>$ajax,
			'options'=>$options
		]);
	}

	/**
	 * @param \Twig_Environment $env
	 * @param $comments
	 * @param array $options
	 * @param bool $canAdminComment
	 * @return string
	 */
	public function commentsList(\Twig_Environment $env,$comments, $canAdminComment=false, $options=[])
	{
		return $env->render('MykeesCommentBundle:Comments:comments.html.twig',[
			'comments'=>$comments,
			"canAdminComment"=>$canAdminComment
		]);
	}


	public function dateInterval($date,$locale)
	{
		$comment_date = new \DateTime($date);
		$dateNow = new \DateTime('NOW');
		$interval = $dateNow->diff($comment_date);

		$periodes = [
			$interval->format('%y'),
			$interval->format('%m'),
			$interval->format('%d'),
			$interval->format('%h'),
			$interval->format('%i')
		];
		$unity_en = [
			['year','years'],
			['month','months'],
			['day','days'],
			['hour','hours'],
			['minute','minutes']
		];
		$unity_fr = [
			['an','ans'],
			['mois','mois'],
			['jour','jours'],
			['heure','heures'],
			['minute','minutes']
		];

		$periodesLength = count($periodes);

		for($i=0; $i < $periodesLength; $i++)
		{
			if(intval($periodes[$i]) >= 1 && $i < count($periodes))
			{
				$locale_unity = $locale == "fr" ? $unity_fr : $unity_en;
				$u = $periodes[$i] > 1 ? $locale_unity[$i][1] : $locale_unity[$i][0];

				return $periodes[$i].' '.$u;
			}
		}
		if($locale == 'fr')
		{
			return "moins d'une minute";
		}else{
			return 'less than a minute';
		}
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName()
	{
		return 'mykees_helper_comment';
	}
}
