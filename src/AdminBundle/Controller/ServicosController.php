<?php


namespace App\AdminBundle\Controller;


use App\Entity\Servico;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ServicosController extends Controller
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->em = $entityManager;
    }

    /**
     * @Route("/listar-jobs", name="admin_listar_jobs")
     * @Template("@Admin/servicos/index.html.twig")
     */
    public function listarJobs(Request $request)
    {
        $status = $request->get('busca_filtro');
        $jobs = $this->em->getRepository(Servico::class)->findByUsuarioAndStatus(null, $status);

        return [
            'status' => $status,
            'microjobs' => $jobs
        ];
    }

}