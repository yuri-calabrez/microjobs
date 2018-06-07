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

     /**
     * @Route("/pagamento/boleto")
     */
    public function pagamentoBoleto(Request $request)
    {
        $contratacao = $this->em->getRepository(Contratacoes::class)->findOneBy(['moip_cod_pedido' => $orderId]);

        $orderId = $request->get('order');
        $moip = $this->get('moip')->getMoip();

        $order = $moip->orders()->get($orderId);

        $logoUri = 'https://cdn.moip.com.br/wp-content/uploads/2016/05/02.png';
        $dataExpiracao = new \DateTime();
        $dataExpiracao->add(new \DateInterval("P3D"));
        $instrucoes = [
            'Pagavel em qualquer banco',
            'apos vencimento somente no banco Xpto',
            'juros não devem ser cobrados'
        ];

        $pagamento = $order
            ->payments()
            ->setBoleto($dataExpiracao->format('Y-m-d'), $logoUri, $instrucoes)
            ->setStatementDescriptor("Pgto Mjobs")
            ->execute();

        $contratacao->setMoipCodPagamento($pagamento->getId());
        $this->em->persist($contratacao);
        $this->em->flush();

        return $this->json(['url_boleto' => $pagamento->getLinks()->getLink('payBoleto')]);
    }

     /**
     * @Route("/pagamento/debito")
     */
    public function pagamentoDebito(Request $request)
    {

        $orderId = $request->get('order');
        $moip = $this->get('moip')->getMoip();

        $contratacao = $this->em->getRepository(Contratacoes::class)->findOneBy(['moip_cod_pedido' => $orderId]);

        $order = $moip->orders()->get($orderId);
        $dataExpiracao = new \DateTime();
        $dataExpiracao->add(new \DateInterval("P3D"));

        $pagamento = $order
            ->payments()
            ->setOnlineBankDebit('341', $dataExpiracao->format('Y-m-d'), "http://localhost:8000")
            ->execute();

        $contratacao->setMoipCodPagamento($pagamento->getId());
        $this->em->persist($contratacao);
        $this->em->flush();

        return $this->json(['url_debito' => $pagamento->getLinks()->getLink('payOnlineBankDebitItau')]);
    }
}
