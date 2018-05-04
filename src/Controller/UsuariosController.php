<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UsuariosController extends Controller
{
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @Route("/usuarios", name="usuarios")
     */
    public function index()
    {
        return $this->render('usuarios/index.html.twig', [
            'controller_name' => 'UsuariosController',
        ]);
    }

    /**
     * @Route("/usuario/login", name="login")
     * @Template("usuarios/login.html.twig")
     */
    public function login(Request $request, AuthenticationUtils $authUtils)
    {
        $error = $authUtils->getLastAuthenticationError();
        $username = $authUtils->getLastUsername();
        return [
            'last_username' => $username,
            'error' => $error
        ];
    }

    /**
     * @Route("/usuario/cadastro", name="cadastrar_usuario")
     * @Template("usuarios/registro.html.twig")
     */
    public function cadastrar(Request $request, \Swift_Mailer $mailer)
    {
        $usuario = new Usuario();
        $form = $this->createForm(UsuarioType::class, $usuario);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            $senhaCrypt = $encoder->encodePassword($usuario, $form->getData()->getPassword());

            $usuario->setSenha($senhaCrypt);
            $token = md5(uniqid());
            $usuario->setToken($token);
            $usuario->setRoles("ROLE_FREELA");
            $this->em->persist($usuario);
            $this->em->flush();

            $mensagem = (new \Swift_Message($usuario->getNome() . ", ative sua conta no Microjobs"))
                ->setFrom('noreplay@microjobs.com.br')
                ->setTo([$usuario->getEmail() => $usuario->getNome()])
                ->setBody($this->renderView('emails/usuarios/registro.html.twig', [
                    'nome' => $usuario->getNome(),
                    'token' => $usuario->getToken()
                ]), 'text/html');
            $mailer->send($mensagem);

            $this->addFlash("success", "Cadastrado com sucesso! Verifique seu e-mail para completar o cadastro");
            return $this->redirectToRoute('default');
        }
        return ['form' => $form->createView()];
    }

    /**
     * @Route("usuario/ativar-conta/{token}", name="email_ativar_conta")
     */
    public function ativarConta($token)
    {
        $usuario = $this->em->getRepository(Usuario::class)->findOneBy(['token' => $token]);
        $usuario->setStatus(true);
        $this->em->persist($usuario);
        $this->em->flush();
        $this->addFlash("success", "Cadastrado ativado com sucesso! Informe seu e-mail e senha para acessar o sistema");
        return $this->redirectToRoute('login');
    }

    /**
     * @Route("painel/usuario/mudar-para-cliente", name="mudar_para_cliente")
     * @Template("usuarios/mudar-para-cliente.html.twig")
     */
    public function mudarParaCliente()
    {
        return [];
    }

    /**
     * @Route("painel/usuario/mudar-para-cliente/confirmar", name="confirmar_mudar_para_cliente")
     */
    public function confirmarMudarParaCliente(UserInterface $user)
    {
        $usuario = $this->em->getRepository(Usuario::class)->find($user);
        $usuario->limparRoles();
        $usuario->setRoles("ROLE_CLIENTE");
        $this->em->persist($usuario);
        $this->em->flush();
        $this->addFlash("success", "Seu perfil foi alterado para Cliente");
        return $this->redirectToRoute('painel');
    }
}
