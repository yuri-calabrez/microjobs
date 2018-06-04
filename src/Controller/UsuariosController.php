<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Form\UsuarioDadosPessoaisType;
use App\Form\UsuarioType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Moip\Exceptions\UnautorizedException;
use Moip\Exceptions\ValidationException;
use Moip\Exceptions\UnexpectedException;
use Moip\Auth\Connect;

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

            $this->get('email')->enviar(
                $usuario->getNome() . ", ative sua conta no Microjobs",
                [$usuario->getEmail() => $usuario->getNome()],
                'emails/usuarios/registro.html.twig', [
                    'nome' => $usuario->getNome(),
                    'token' => $usuario->getToken()
                ]
            );

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

    /**
     * @Route("painel/usuario/dados-pessoais", name="dados_pessoais")
     * @Template("usuarios/dados-pessoais.html.twig")
     */
    public function dadosPessoais(UserInterface $user, Request $request)
    {
        $usuario = $this->em->getRepository(Usuario::class)->find($user);
        $form = $this->createForm(UsuarioDadosPessoaisType::class, $usuario);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$usuario->getDadosPessoais()->setDataAlteracao(new \DateTime());
            $this->em->persist($usuario);
            $this->em->flush();
            $this->addFlash("success", "Seus dados pessoais foram alterados com sucesso!");
            return $this->redirectToRoute('painel');
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     *  @Route("painel/usuario/auth-moip")
     */
    public function getAuthMoip()
    {
        try {
            $redirUri = $this->getParameter('moip_url_retorno');
            $appId = $this->getParameter('moip_app_id');
            $scope = true;
            $connect = new Connect($redirUri, $appId, $scope, Connect::ENDPOINT_SANDBOX);
            $connect
                ->setScope(Connect::RECEIVE_FUNDS)
                ->setScope(Connect::REFUND)
                ->setScope(Connect::MANAGE_ACCOUNT_INFO)
                ->setScope(Connect::RETRIEVE_FINANCIAL_INFO);

            return $this->json(['url' => $connect->getAuthUrl()]);

        } catch (UnautorizedException $e) {
            echo $e->getMessage();
            exit;
        } catch (ValidationException $e) {
            echo $e->getMessage();
            exit;
        } catch (UnexpectedException $e) {
            echo $e->getMessage();
            exit;
        }
    }

    
    /**
     *  @Route("painel/usuarios/autorizar-moip")
     */
    public function getCodeMoip(Request $request, UserInterface $user)
    {
        $code = $request->get('code');
        try {
            $redirUri = $this->getParameter('moip_url_retorno');
            $appId = $this->getParameter('moip_app_id');
            $scope = true;
            $connect = new Connect($redirUri, $appId, $scope, Connect::ENDPOINT_SANDBOX);

           $secret = $this->getParameter('moip_secret');
           $connect->setClientSecret($secret);
           $connect->setCode($code);

           $auth = $connect->authorize();

           $usuario = $this->em->getRepository(Usuario::class)->find($user);
           $usuario->getDadosPessoais()->setMoipAccessToken($auth->access_token); 
           $usuario->getDadosPessoais()->setMoipIdConta($auth->moipAccount->id); 
           $this->em->persist($usuario);
           $this->em->flush();

           $this->addFlash("success", "Sua conta Moip fom vinculada com sucesso!");
           return $this->redirectToRoute('painel');

        } catch (UnautorizedException $e) {
            echo $e->getMessage();
            exit;
        } catch (ValidationException $e) {
            echo $e->getMessage();
            exit;
        } catch (UnexpectedException $e) {
            echo $e->getMessage();
            exit;
        }
    }
}
