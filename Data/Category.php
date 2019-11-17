<?php

class Category
{

   private $categoryName;
   private $categoryImage;

    /**
     * Category constructor.
     * @param $categoryName
     * @param $categoryImage
     */
    public function __construct($categoryName, $categoryImage)
    {
        $this->categoryName = $categoryName;
        $this->categoryImage = $categoryImage;
    }


    public function getCategoryName()
    {
        return $this->categoryName;
    }
 public function getCategoryImage()
    {
        return $this->categoryImage;
    }



}