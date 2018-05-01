<?php

namespace App\Controller;

use App\Entity\Servico;
use App\Form\ServicoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;

class ServicosController extends Controller
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @Route("/servicos", name="servicos")
     */
    public function index()
    {
        return $this->render('servicos/index.html.twig', [
            'controller_name' => 'ServicosController',
        ]);
    }

    /**
     * @Route("/painel/servicos/cadastrar", name="cadastrar_job")
     * @Template("servicos/novo-micro-jobs.html.twig")
     */
    public function cadastrar(Request $request, UserInterface $user)
    {
        $servico = new Servico();
        $form = $this->createForm(ServicoType::class, $servico);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imagem = $servico->getImagem();
            $nomeArquivo = md5(uniqid()).".".$imagem->guessExtension();
            $imagem->move($this->getParameter('caminho_img_job'), $nomeArquivo);
            $servico->setImagem($nomeArquivo);

            $servico->setValor(30.00);
            $servico->setUsuario($user);
            $servico->setStatus("A");

            $this->em->persist($servico);
            $this->em->flush();

            $this->addFlash('success', 'Cadastrado com sucesso!');
            return $this->redirectToRoute('painel');
        }

        return ['form' => $form->createView()];
    }

    /**
     * @Route("/painel/servicos/excluir/{id}", name="excluir_job")
     */
    public function excluir(Servico $servico)
    {
        $servico->setStatus("E");
        $this->em->persist($servico);
        $this->em->flush();

        $this->addFlash('success', 'Serviço excluido com sucesso!');
        return $this->redirectToRoute('painel');
    }
}
