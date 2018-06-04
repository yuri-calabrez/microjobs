<?php

namespace App\Controller;

use Moip\Auth\BasicAuth;
use Moip\Moip;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PagamentoController extends Controller
{
    /**
     * @Route("/pagamento", name="pagamento")
     */
    public function index()
    {
        $moip = new Moip(new BasicAuth(getenv('MOIP_TOKEN'), getenv('MOIP_KEY')), MOIP::ENDPOINT_SANDBOX);
        dump($moip);
        return $this->render('pagamento/index.html.twig', [
            'controller_name' => 'PagamentoController',
        ]);
    }
}
