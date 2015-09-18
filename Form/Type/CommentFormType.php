<?php

namespace Mykees\CommentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CommentFormType extends AbstractType
{

	protected $tokenStorage;
	public $user;
	public $username;
	public $email;
	public $content;

	public function __construct(TokenStorageInterface $tokenStorage)
	{
		$this->tokenStorage = $tokenStorage->getToken();
		$this->user = method_exists($this->tokenStorage,'getUser') ? $this->tokenStorage->getUser() : array();
	}
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
	    if(empty($this->user) || $this->user == 'anon.')
	    {
		    $builder
			    ->add('username','text',[
				    'label'=> $this->username
			    ])
			    ->add('email','text',[
				    'label'=> $this->email
			    ])
			    ->add('content','textarea',[
				    'label'=> $this->content
			    ])
			    ->add('parentId','hidden',['data'=>0])
			    ->add('model','hidden')
			    ->add('modelId','hidden')
		    ;
	    }else{
		    $builder
			    ->add('username','hidden',[
				    'data'=>$this->user->getUsername()
			    ])
			    ->add('email','hidden',[
				    'data'=>$this->user->getEmail()
			    ])
			    ->add('content')
			    ->add('parentId','hidden',['data'=>0])
			    ->add('model','hidden')
			    ->add('modelId','hidden')
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
        return 'mykees_comment';
    }
}
