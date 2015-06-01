<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 19:14
 */

namespace Mykees\CommentBundle\Twig\Extension;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;

class CommentExtension extends \Twig_Extension{


    /**
     * Container
     *
     * @var ContainerInterface
     */
    protected $templating;

    /**
     * Initialize tinymce helper
     *
     * @param EngineInterface $templating
     */
    public function __construct(EngineInterface $templating)
    {
        $this->templating = $templating;
    }


    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'helper_comment' => new \Twig_Function_Method($this, 'commentForm', array('is_safe' => array('html'))),
            'comments_list'=> new \Twig_Function_Method($this, 'commentsList', array('is_safe' => array('html'))),
        );
    }

    public function getFilters() {
        return array(
            'url_decode' => new \Twig_Filter_Method($this, 'urlDecode')
        );
    }

    public function commentForm($form)
    {
        return $this->templating->renderResponse('MykeesCommentBundle:Comment:form.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    public function commentsList($comments,$canAdminComment=false)
    {
        return $this->templating->renderResponse('MykeesCommentBundle:Comment:comments.html.twig',[
            'comments'=>$comments,
            "canAdminComment"=>$canAdminComment
        ]);
    }


    public function getName()
    {
        return 'mykees_helper_form';
    }
}