<?php

namespace App\Controller;

use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends AbstractController
{
    protected function getTo(Request $request): DateTimeImmutable
    {
        return $request->get('to')
            ? DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $request->get('to').'23:59:59.999')
            : new DateTimeImmutable();
    }
}
