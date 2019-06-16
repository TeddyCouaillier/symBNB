<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class FrenchToDateTimeTransformer implements DataTransformerInterface
{
    public function transform($date)
    {
        return $date === null ? '' : $date->format('d/m/Y');
    }

    public function reverseTransform($frenchDate)
    {
        if($frenchDate === null){
            throw new TransformationFailedException("Date non fournie");
        }

        $date = \DateTime::createFromFormat('d/m/Y',$frenchDate);
        
        if($date === false) {
            throw new TransformationFailedException("Format de date invalide");
        }

        return $date;
    }
}