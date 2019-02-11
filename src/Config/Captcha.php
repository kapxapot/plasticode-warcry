<?php

namespace App\Config;

class Captcha
{
    public function getReplaces()
    {
        return [
    		'ард' => [ 'ярд' ],
    		// .. your rules here. mine are mine ;)
    	];
    }
}
