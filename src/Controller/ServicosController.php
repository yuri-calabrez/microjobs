<?php

namespace App\Controller;

use App\Entity\Contratacoes;
use App\Entity\Servico;
use App\Entity\Usuario;
use App\Form\ServicoType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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

    /**
     * @Route("/contratar-servico/{id}/{slug}/tela-pagamento", name="tela_pagamento")
     * @Template("servicos/tela-pagamento.html.twig")
     */
    public function telaPagamento(Servico $servico, UserInterface $user)
    {
        if ($user->getRoles()[0] == 'ROLE_FREELA') {
            $this->addFlash('warning', "<h3>Atenção!</h3><p>Para contratar um serviço precisa ser um ciente. 
                                                                        Acesse seu painel e faça a migração gratuitamente.</p>");
            return $this->redirectToRoute('painel');
        }

        $data = [];
        $form = $this
                ->createFormBuilder($data)
                ->add('numero', TextType::class, [
                    'label' => 'Número do cartão'
                ])
                ->add('mes_expiracao', TextType::class, [
                    'label' => 'Mês'
                ])
                ->add('ano_expiracao', TextType::class, [
                    'label' => 'Ano'
                ])
                ->add('cod_seguranca', TextType::class, [
                    'label' => 'CVV'
                ])
                ->add('enviar', ButtonType::class, [
                    'label' => 'Realizar pagamento',
                    'attr' => [
                        'class' => 'btn btn-primary'
                    ]
                ])
                ->getForm();
        return [
            'job' => $servico,
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/contratar-servico/{id}/{slug}", name="contratar_servico")
     */
    public function contratarServico(Servico $servico, UserInterface $user)
    {
        //TODO: VERIFICAR ACESSO SOMENTE SE LOGADO

        $contratacao = new Contratacoes();
        $contratacao
            ->setValor($servico->getValor())
            ->setServico($servico)
            ->setCliente($user)
            ->setFreelancer($servico->getUsuario())
            ->setStatus("A");

        $this->em->persist($contratacao);
        $this->em->flush();
        $this->get('email')->enviar(
            $user->getNome()." - Contratação de serviço",
            [$user->getEmail() => $user->getNome()],
            'emails/servicos/contratacao-cliente.html.twig',
            ['servico' => $servico, 'cliente' => $user]
        );

        $this->get('email')->enviar(
            $servico->getUsuario()->getNome().", Parabens",
            [$servico->getUsuario()->getEmail() => $servico->getUsuario()->getNome()],
            'emails/servicos/contratacao-freela.html.twig',
            ['servico' => $servico, 'cliente' => $user]
        );

        $this->addFlash('success', 'Serviço foi contratado!');
        return $this->redirectToRoute('default');
    }

    /**
     * @Route("/painel/servicos/listar-compras", name="listar_compras")
     * @Template("servicos/listar-compras.html.twig")
     */
    public function listarCompras(UserInterface $user)
    {
        $usuario = $this->em->getRepository(Usuario::class)->find($user);
        return [
            'compras' => $usuario->getCompras()
        ];
    }

    /**
     * @Route("/painel/servicos/listar-vendas", name="listar_vendas")
     * @Template("servicos/listar-vendas.html.twig")
     */
    public function listarVendas(UserInterface $user)
    {
        $usuario = $this->em->getRepository(Usuario::class)->find($user);
        return [
            'vendas' => $usuario->getVendas()
        ];
    }
}
