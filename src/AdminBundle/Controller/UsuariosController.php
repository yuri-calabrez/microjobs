<?php


namespace App\AdminBundle\Controller;


use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class UsuariosController extends Controller
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->em = $entityManager;
    }

    /**
     * @Route("/usuarios/listar", name="admin_listar_usuarios")
     * @Template("@Admin/usuarios/index.html.twig")
     */
    public function listar(Request $request)
    {
        $status = $request->get('status');

        if ($status === "" || is_null($status)) {
            $usuarios = $this->em->getRepository(Usuario::class)->findAll();
        } else {
            $usuarios = $this->em->getRepository(Usuario::class)->findBy(['status' => $status]);
        }

        return [
            'usuarios' => $usuarios,
            'status' => $status
        ];
    }
}