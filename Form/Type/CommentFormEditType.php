<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 08/09/2015
 * Time: 14:30
 */

namespace Mykees\CommentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommentFormEditType extends AbstractType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		if(empty($this->user) || $this->user == 'anon.')
		{
			$builder
				->add('username')
				->add('email')
				->add('content')
				->add('model',"text",[
					'required'=>false
				])
				->add('modelId',"text",[
					'required'=>false
				])
				->add('parentId',"text",[
					'required'=>false
				])
				->add('spam',"text",[
					'required'=>false,
					'data'=>0
				])
				->add('ip',"text",[
					'required'=>false
				])
			;
		}
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Mykees\CommentBundle\Entity\Comment'
		));
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'mykees_comment_edit';
	}
}
