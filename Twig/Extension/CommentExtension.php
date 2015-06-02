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
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('helper_comment', [$this, 'commentForm'], [
                'is_safe'=>array('html'),
                'needs_environment'=>true
            ]),
            new \Twig_SimpleFunction('comments_list', [$this, 'commentsList'], [
                'is_safe'=>['html'],
                'needs_environment'=>true
            ]),
        );
    }

    public function getFilters() {
        return array(
            'url_decode' => new \Twig_Filter_Method($this, 'urlDecode')
        );
    }

    public function commentForm(\Twig_Environment $env,$form)
    {
        return $env->render('MykeesCommentBundle:Comment:form.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    public function commentsList(\Twig_Environment $env,$comments,$canAdminComment=false)
    {
        return $env->render('MykeesCommentBundle:Comment:comments.html.twig',[
            'comments'=>$comments,
            "canAdminComment"=>$canAdminComment
        ]);
    }


    public function getName()
    {
        return 'mykees_helper_form';
    }

}
