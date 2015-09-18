<?php
/**
 * Created by PhpStorm.
 * User: Rafidion Michael
 * Date: 03/09/2015
 * Time: 12:02
 */

namespace Mykees\CommentBundle\Manager;


abstract class Manager {


	/**
	 * @param $ref
	 * @return string
	 */
	public function getBundleShortName ( $ref ){
		$explode = explode('\\', $this->getClassName($ref));
		return $explode[0].$explode[1];
	}


	/**
	 * @param $ref
	 * @return string
	 */
	public function getBundlePath ( $ref ){
		$explode = explode('\\', $this->getClassName($ref));
		return $explode[0].'\\'.$explode[1];
	}


	/**
	 * @param $ref
	 * @param bool $withouSlash
	 * @return string
	 */
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


	/**
	 * @param $ref
	 * @return string
	 */
	public function getClassShortName( $ref ) {
		$reflection = new \ReflectionClass( $ref );
		if( $reflection->getParentClass() ) {
			$reflection = $reflection->getParentClass();
		}
		return $reflection->getShortName();
	}


	/**
	 * @param $ref
	 * @return string
	 */
	public function getClassName ( $ref ) {
		$reflection = new \ReflectionClass( $ref );
		return $reflection->getName();
	}

	/**
	 * Retrieve class repository name
	 * @param $comment_class
	 * @return string
	 */
	public function classRepository($comment_class)
	{
		$comment_class_name = $comment_class;
		$explode = explode('\\',$comment_class_name);

		return $explode[0] . $explode[1] . ":" . $explode[3];
	}
}