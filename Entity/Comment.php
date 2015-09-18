<?php

namespace Mykees\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mykees\CommentBundle\Interfaces\Commentable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
abstract class Comment implements Commentable
{
	/**
	 * @var string
	 *
	 * @ORM\Column(name="username", type="string", length=100)
	 * @Assert\NotBlank(message="Vous devez entre un Nom")
	 */
	private $username;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="email", type="string", length=255)
	 * @Assert\Email(message="L'Email n'est pas valid")
	 * @Assert\NotBlank(message="Vous devez préciser un Email.")
	 */
	private $email;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="content", type="text")
	 * @Assert\NotBlank(message="Vous devez entrer un Message.")
	 */
	private $content;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(name="created_at", type="datetime")
	 * @Assert\DateTime()
	 */
	private $createdAt;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="model", type="string", length=60)
	 */
	private $model;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="model_id", type="integer")
	 */
	private $modelId;

	/**
	 * @var integer
	 *
	 * @ORM\Column(name="parent_id", type="integer")
	 */
	private $parentId;

	/**
	 * @var boolean
	 *
	 * @ORM\Column(name="spam", type="boolean")
	 */
	private $spam;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="ip", type="string", length=255)
	 */
	private $ip;

	private $depth = 0;

	private $depthReached;


	private $children = [];

	/**
	 * @ORM\PrePersist
	 */
	public function updateDate()
	{
		$this->setCreatedAt(new \DateTime());
	}

	/**
	 * Set username
	 *
	 * @param string $username
	 * @return Comment
	 */
	public function setUsername($username)
	{
		$this->username = $username;

		return $this;
	}

	/**
	 * Get username
	 *
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set email
	 *
	 * @param string $email
	 * @return Comment
	 */
	public function setEmail($email)
	{
		$this->email = $email;

		return $this;
	}

	/**
	 * Get email
	 *
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Set content
	 *
	 * @param string $content
	 * @return Comment
	 */
	public function setContent($content)
	{
		$this->content = $content;

		return $this;
	}

	/**
	 * Get content
	 *
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Set createdAt
	 *
	 * @param \DateTime $createdAt
	 * @return Comment
	 */
	public function setCreatedAt($createdAt)
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Get createdAt
	 *
	 * @return \DateTime
	 */
	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	/**
	 * Set model
	 *
	 * @param string $model
	 * @return Comment
	 */
	public function setModel($model)
	{
		$this->model = $model;

		return $this;
	}

	/**
	 * Get model
	 *
	 * @return string
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Set modelId
	 *
	 * @param integer $modelId
	 * @return Comment
	 */
	public function setModelId($modelId)
	{
		$this->modelId = $modelId;

		return $this;
	}

	/**
	 * Get modelId
	 *
	 * @return integer
	 */
	public function getModelId()
	{
		return $this->modelId;
	}

	/**
	 * Set parentId
	 *
	 * @param integer $parentId
	 * @return Comment
	 */
	public function setParentId($parentId)
	{
		$this->parentId = $parentId;

		return $this;
	}

	/**
	 * Get parentId
	 *
	 * @return integer
	 */
	public function getParentId()
	{
		return $this->parentId;
	}

	/**
	 * Set spam
	 *
	 * @param boolean $spam
	 * @return Comment
	 */
	public function setSpam($spam)
	{
		$this->spam = $spam;

		return $this;
	}

	/**
	 * Get spam
	 *
	 * @return boolean
	 */
	public function getSpam()
	{
		return $this->spam;
	}

	/**
	 * Set ip
	 *
	 * @param string $ip
	 * @return Comment
	 */
	public function setIp($ip)
	{
		$this->ip = $ip;

		return $this;
	}

	/**
	 * Get ip
	 *
	 * @return string
	 */
	public function getIp()
	{
		return $this->ip;
	}

	/**
	 * @return mixed
	 */
	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * @param mixed $children
	 */
	public function setChildren(Comment $children)
	{
		array_unshift($this->children,$children);
	}

	/**
	 * @return int
	 */
	public function getDepth()
	{
		return $this->depth;
	}

	/**
	 * @param int $depth
	 */
	public function setDepth($depth)
	{
		$this->depth = $depth;
	}

	/**
	 * @return mixed
	 */
	public function getDepthReached()
	{
		return $this->depthReached;
	}

	/**
	 * @param mixed $depthReached
	 */
	public function setDepthReached($depthReached)
	{
		$this->depthReached = $depthReached;
	}

}
