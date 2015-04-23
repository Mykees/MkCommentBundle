<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 13:04
 */

namespace Mykees\CommentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mykees\CommentBundle\Interfaces\CommentModelInterface;
use Mykees\CommentBundle\Traits\CommentTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\MappedSuperclass
 */
class Comment implements CommentModelInterface {
    use CommentTrait;
}