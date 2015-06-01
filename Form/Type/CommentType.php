<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 13:50
 */

namespace Mykees\CommentBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

class CommentType extends AbstractType {

    public $commentClass;
    public $user;
    public $context;

    public function __construct($commentClass=null, SecurityContextInterface $context)
    {
        if(is_object($commentClass))
        {
            $reflection = new \ReflectionClass($commentClass);
            $this->commentClass = $reflection->getName();
        }else{
            $this->commentClass = $commentClass;
        }
        $this->context = $context;
        $security_token = $this->context->getToken();
        $this->user = method_exists($security_token,'getUser') ? $security_token->getUser() : array();
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
                ->add('username')
                ->add('email','email')
                ->add('content')
                ->add('parentId','hidden',['data'=>0])
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
            ;
        }
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->commentClass,
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'mykees_commentbundle_comment';
    }

}
