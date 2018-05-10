<?php

namespace App\Utils\Slugger;

class SluggerUtil
{
    public function makeSlug(string $str)
    {
        $slug = strtolower($str);
        $slug = preg_replace("/[^a-z0-9_\s-]/", "", $slug);
        $slug = preg_replace("/[\s_]/", "-", $slug);
        
        return $slug;
    }
}