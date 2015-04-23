<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 13:07
 */

namespace Mykees\CommentBundle\Interfaces;


interface CommentModelInterface {

    public function setUsername($username);

    public function getUsername();

    public function setEmail($email);

    public function getEmail();

    public function setContent($content);

    public function getContent();

    public function setCreatedAt($createdAt);

    public function getCreatedAt();

    public function setModel($model);

    public function getModel();


    public function setModelId($modelId);


    public function getModelId();


    public function setParentId($parentId);


    public function getParentId();


}