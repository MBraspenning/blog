<?php

namespace App\Utils\Search;

use Symfony\Component\HttpFoundation\Request;
use App\Form\SearchType;

class SearchUtil
{
    private $FormFactory;
    
    public function __construct($FormFactory)
    {
        $this->FormFactory = $FormFactory;
    }
    
    public function createSearchForm()
    {
        return $this->FormFactory->create(SearchType::class);
    }
    
    public function handleSearchForm(Request $request, $search_form)
    {
        $search_form->handleRequest($request);
        
        if ($search_form->isSubmitted() && $search_form->isValid())
        {
            $searchQuery = $search_form->getData()['Search'];
            
            return $searchQuery;
        }
    }
}