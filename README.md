# Symfony2-CommentBundle


## Presentation

The Symfony2-CommentBundle bundle allows you to add a form that will allow you to comment on your content and then to retrieve these comments. All this very easily.

## Intallation



**1)** Download Symfony2-CommentBundle with the command :

``` $ php composer.phar require mykees/symfony2-commentbundle "1.0.*@dev" ```



**2)** Enable the bundle:

	<?php

	public function registerBundle
	{
		$bundles = array(
	        // ...
	        new Mykees\CommentBundle\MykeesCommentBundle(),
	    );
	}


**3)** Add route in your app/config/routing.yml:

	mykees_comment:
        resource: "@MykeesCommentBundle/Resources/config/routing.yml"
        prefix:   /comment




## Create your Comment classe

**1)** You must to create your own Comment class for use the Symfony2-CommentBundle, and this class must extend by the *Mykees\CommentBundle\Entity\Comment* class that will provide variables getters, setters and mapping required.

	<?php
	// src/YourProject/YourBundle/Entity/Comment.php

	namespace YourProject\YourBundle\Entity;

	use Doctrine\ORM\Mapping as ORM;
	use Mykees\CommentBundle\Entity\Comment as CommentParent;

	/**
	 * @ORM\Entity
	 */
	class Comment extends CommentParent
	{
	        /**
		     * @var integer
		     *
		     * @ORM\Column(name="id", type="integer")
		     * @ORM\Id
		     * @ORM\GeneratedValue(strategy="AUTO")
		     */
		    private $id;


		    /**
		     * Get id
		     *
		     * @return integer 
		     */
		    public function getId()
		    {
		        return $this->id;
		    }
	}


**2)** Enable the configuration in your app/config/config.yml:

	mykees_comment:
	    comment_class: YourProject\YourBundle\Entity\Comment


## Retrieves and use the comment form

To retrieves the comment form for your content, you need to have an object with an id. By example if you have a controller that retrieves an article and an entity Article, you can do like that:

**1)** In your controller :

	<?php

	namespace YourProject\YourBundle\Controller;

	use Symfony\Bundle\FrameworkBundle\Controller\Controller;

	class BlogController extends Controller
	{

		public function showAction($id)
	    {
	        $post = $this->getDoctrine()->getManager()->getRepository('YourProjectYourBundle:Article')->findById($id);
	        
	        $form = $this->get('mykees.comment.manager')->createForm($article);

	        return $this->render('YourProjectYourBundle:Blog:show.html.twig',[
	            //...
	            'form'=>$form,
	        ]);
	    }

	}

**2)** In your Article entity you need to implement the *CommentableInterface* Interface :

	<?php

	namespace YourProject\YourBundle\Entity;

	use Doctrine\ORM\Mapping as ORM;
	use Mykees\CommentBundle\Interfaces\CommentableInterface;

	/**
	 * Article
	 *
	 * @ORM\Table()
	 * @ORM\Entity()
	 */
	class Article implements CommentableInterface
	{
		//....
	}

## Retrieves comments list

To retrieves the comments list linked to an entity you can do like that in your controller :

**1)** In your controller :

	<?php

	namespace YourProject\YourBundle\Controller;

	class BlogController extends Controller
	{
		public function showAction($id)
	    {
	        $post = $this->getDoctrine()->getManager()->getRepository('YourProjectYourBundle:Article')->findById($id);

        	$comments = $this->get('mykees.comment.manager')->findComments($article);

	        return $this->render('YourProjectYourBundle:Blog:show.html.twig',[
	            //...
	            'comments'=>$comments,
	        ]);
	    }
	}


## View and Style


In your view, to add the comment form and comments list you can do this very easily. You just need to use 2 helpers.

**1)** Form helper:
	
	//...

	{{ helper_comment(form) }}

	//...

**2)** Comments list helper: 

	//...

	{{ comments_list(comments) }}

	//...


**3)** To enhance the style and user experience you must to add the css and javascript markup :

Run the following command lines:

	$ php app/console assets:install

**CSS**
	
	// app/Resources/base.html.twig

    <head>
		//...

		{% block stylesheets %}
	            <link rel="stylesheet" href="{{ asset('bundles/mykeescomment/css/comment.css') }}">
	    {% endblock %}

	    //...
    </head>


**Javascript**

	// src/YourProject/YourBundle/Resources/views/Blog/show.html.php

		{% extends 'base.html.twig' %}

		{% block body %}
		    //...
		{% endblock %}



		{% block javascripts %}
		    {{ parent() }}
		    <script type="text/javascript" src="{{ asset('bundles/mykeescomment/js/comment.js') }}"></script>
		{% endblock %}




That's it! Enjoy :)



# Optional


## Use with FOSUserBundle

If you use the FOSUserBundle in your project and want to use it with Symfony2-CommentBundle, your Comment classe must implement *HasUserInterface* Inteface, and add *$user* variable with *Getter* and *Setter*

	<?php

	namespace Mvc\BlogBundle\Entity;

	use Doctrine\ORM\Mapping as ORM;
	use FOS\UserBundle\Model\UserInterface;
	use Mykees\CommentBundle\Entity\Comment as BaseComment;
	use Mykees\CommentBundle\Interfaces\HasUserInterface;

	/**
	 * Comment
	 *
	 * @ORM\Entity
	 */
	class Comment extends BaseComment implements HasUserInterface
	{

		//...

		/**
	     *
	     * @ORM\ManyToOne(targetEntity="YourProject\YourBundle\Entity\User")
	     * @var User
	     */
	    private $user;



	    //...


	    public function getUser()
	    {
	        return $this->user;
	    }

	    public function setUser(UserInterface $user)
	    {
	        $this->user = $user;
	        return $this;
	    }
		
		//...
	}

## Spam Filter

The Symfony2-CommentBundle can work with Akismet. To use it, it's very simple.

**1)** Create an [account](https://akismet.com/).


**2)** Add to app/config/config.yml the following lines:

	mykees_comment:
	    akismet:
	        api_key: YOUR API KEY
	        website: YOUR WEBSITE


