<?php


namespace App\AdminBundle\Controller;

use App\Entity\Contratacoes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class RelatoriosController extends Controller
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
     * @Route("/relatorios/faturamento", name="admin_relatorio_faturamento")
     */
    public function faturamento(Request $request)
    {
        $exportar = $request->get('exportar');
        $faturamento = $this->em->getRepository(Contratacoes::class)->retornaFaturamento();
        if ($exportar === "pdf") {
            $html = $this->renderView("@Admin/relatorios/relatorio-faturamento.html.twig", ['faturamentos' => $faturamento]);

            $dompdf = $this->get('dompdf');
            $dompdf->streamHtml($html, "relatorio-faturamento.pdf");
        } else {
            return $this->render("@Admin/relatorios/faturamento.html.twig", ['faturamentos' => $faturamento]);
        }
    }

}