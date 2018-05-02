<?php

namespace App\Controller;

use App\Entity\Categoria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class CategoriasController extends Controller
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }
    /**
     * @Route("/categorias", name="categorias")
     */
    public function index()
    {
        return [];
    }

    /**
     * @Template("categorias/listar_topo.html.twig")
     */
    public function listarTopo()
    {
        $categorias = $this->em->getRepository(Categoria::class)->findAll();
        return [
            'categorias' => $categorias
        ];
    }
}
