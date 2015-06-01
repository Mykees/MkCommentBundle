<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 19:14
 */

namespace Mykees\CommentBundle\Twig\Extension;


class CommentExtension extends \Twig_Extension{


    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Initialize tinymce helper
     *
     * @param ContainerInterface|\Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Gets a service.
     *
     * @param string $id The service identifier
     *
     * @return object The associated service
     */
    public function getService($id)
    {
        return $this->container->get($id);
    }


    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'helper_comment' => new \Twig_Function_Method($this, 'CommentForm', array('is_safe' => array('html'))),
            'comments_list'=> new \Twig_Function_Method($this, 'CommentsList', array('is_safe' => array('html'))),
        );
    }

    public function getFilters() {
        return array(
            'url_decode' => new \Twig_Filter_Method($this, 'urlDecode')
        );
    }

    public function CommentForm($form)
    {
        return $this->getService('templating')->render('MykeesCommentBundle:Comment:form.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    public function CommentsList($comments,$canAdminComment=false)
    {
        return $this->getService('templating')->render('MykeesCommentBundle:Comment:comments.html.twig',[
            'comments'=>$comments,
            "canAdminComment"=>$canAdminComment
        ]);
    }


    public function getName()
    {
        return 'mykees_helper_form';
    }
}