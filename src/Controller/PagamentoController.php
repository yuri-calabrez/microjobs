<?php

namespace App\Controller;

use Moip\Auth\BasicAuth;
use Moip\Moip;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Moip\Exceptions\UnautorizedException;
use Moip\Exceptions\ValidationException;
use Moip\Exceptions\UnexpectedException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Contratacoes;

class PagamentoController extends Controller
{

    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }
    /**
     * @Route("/pagamento", name="pagamento")
     */
    public function index()
    {
        return $this->render('pagamento/index.html.twig', [
            'controller_name' => 'PagamentoController',
        ]);
    }

    
    /**
     * @Route("/pagamento/cartao-credito")
     */
    public function pagamentoCartaoCredito(Request $request)
    {
        $hash = $request->get('hash');
        $orderId = $request->get('order');

        try {
            $contratacao = $this->em->getRepository(Contratacoes::class)->findOneBy(['moip_cod_pedido' => $orderId]);

            $moip = $this->get('moip')->getMoip();

            $customerCodeMoip = $contratacao->getCliente()->getDadosPessoais()->getCodMoip();
            $customer = $moip->customers()->get($customerCodeMoip);

            $order = $moip->orders()->get($orderId);
            $cliente = $contratacao->getCliente();

            $holder = $moip
                ->holders()
                ->setFullName($cliente->getNome())
                ->setPhone($cliente->getDadosPessoais()->getTelefoneDdd(), $cliente->getDadosPessoais()->getTelefoneNumero())
                ->setTaxDocument($cliente->getDadosPessoais()->getCpf())
                ->setBirthDate($cliente->getDadosPessoais()->getDataNascimento())
                ->setAddress('BILLING',
                        $cliente->getDadosPessoais()->getLogradouro(),
                        $cliente->getDadosPessoais()->getEnderecoNumero(),
                        $cliente->getDadosPessoais()->getBairro(),
                        $cliente->getDadosPessoais()->getCidade(),
                        $cliente->getDadosPessoais()->getEstado(),
                        '12345678'
                );

            $pagamento = $order
                ->payments()
                ->setCreditCardHash($hash, $holder)
                ->setInstallmentCount(1)
                ->setStatementDescriptor('Pagto Mjobs')
                ->execute();

            $contratacao->setMoipCodPagamento($pagamento->getId());
            $this->em->persist($contratacao);
            $this->em->flush();

            return $this->json(['message' => "Seu pagamento foi realizado com sucesso! Verifique sua conta Moip para mais detalhes."]);


        } catch (UnautorizedException $e) {
            echo $e->getMessage();
            exit;
        } catch (ValidationException $e) {
            var_dump($e->__toString());
            exit;
        } catch (UnexpectedException $e) {
            echo $e->getMessage();
            exit;
        }
    }
}
