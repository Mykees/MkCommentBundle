<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 21/04/2015
 * Time: 15:30
 */

namespace Mykees\CommentBundle\Manager;


abstract class Manager {

    public function getBundleShortName ( $ref ){
        $explode = explode('\\', $this->getClassName($ref));
        return $explode[0].$explode[1];
    }

    public function getBundlePath ( $ref ){
        $explode = explode('\\', $this->getClassName($ref));
        return $explode[0].'\\'.$explode[1];
    }

    public function getFullBundlePath($ref,$withouSlash = false)
    {
        $explode = explode('\\', $this->getClassName($ref));
        if($withouSlash)
        {
            return $explode[0].$explode[1].':'.$explode[3];
        }else{
            return $explode[0].'\\'.$explode[1].'\\'.$explode[2].'\\'.$explode[3];
        }
    }

    public function getClassShortName ( $ref ) {
        $reflection = new \ReflectionClass( $ref );

        if( $reflection->getParentClass() ) {
            $reflection = $reflection->getParentClass();
        }

        return $reflection->getShortName();
    }

    public function primaryKey ( $ref ) {
        $bundleName = $this->getBundleShortName($ref);
        $class 		= $this->getClassShortName ( $ref );
        $meta 		= $this->em->getClassMetadata("$bundleName:$class");
        $pk 		= $meta->getSingleIdentifierFieldName();

        $getter     = "get".ucfirst($pk);
        return $getter;
    }

    public function getClassName ( $ref ) {
        $reflection = new \ReflectionClass( $ref );

        return $reflection->getName();
    }



    protected function findComment()
    {

    }
}