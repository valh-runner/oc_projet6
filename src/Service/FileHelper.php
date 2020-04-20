<?php

namespace App\Service;

class FileHelper
{

    /**
    * Transform a filename to a unique filename
    */
    public function getUniqueFilename($originalFilename)
    {
        $mainPartFilename = pathinfo($originalFilename, PATHINFO_FILENAME); // original filename
        $pattern = 'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()'; // transliteration pattern
        $mainPartFilename = transliterator_transliterate($pattern, $mainPartFilename); // reformated filename
        $filenameExtension = pathinfo($originalFilename, PATHINFO_EXTENSION); // extension part
        return $mainPartFilename.'-'.uniqid().'.'.$filenameExtension; // unique reformated filename with extension
    }

}
