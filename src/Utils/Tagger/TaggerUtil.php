<?php

namespace App\Utils\Tagger;

class TaggerUtil
{
    public function makeTags($categories)
    {
        $tags = "";
        
        foreach($categories as $categorie)
        {
            $tags .= $categorie->getName() . ', ';
        }
        
        $tags = trim($tags, ', ');
        
        return $tags;
    }
}