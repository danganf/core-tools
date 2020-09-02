<?php

namespace DanganfTools\MyClass;

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ThrowNewException
{
    public function Unauthorized($message=''){
        throw new \InvalidArgumentException($message);
    }

    public function NotFoundHtt($message=''){
        throw new NotFoundHttpException($message, new \Exception($message), 404);
    }

    public function MethodNotAllowedHttp($message=''){
        throw new MethodNotAllowedHttpException([],$message, new \Exception($message),405);
    }

    public function InvalidArgument($message=''){
        throw new \InvalidArgumentException($message);
    }

    public function PreconditionRequired($message=''){
        throw new \InvalidArgumentException($message, 428);
    }

    public function setReturnValues($values){
        \Request::merge(['exceptionReturn' => $values]);
        return $this;
    }

}