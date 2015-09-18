<?php
namespace Mykees\CommentBundle\Twig\Extension;

class Md5Extension extends \Twig_Extension
{


	public function getFilters() {
		return array(
			'md5' => new \Twig_Filter_Method($this, 'md5')
		);
	}


	public function md5 ( $val ){
		return md5($val);
	}

	public function getName() {
		return 'md5';
	}

}
