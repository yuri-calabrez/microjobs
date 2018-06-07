<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContratacoesRepository")
 */
class Contratacoes
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Servico", inversedBy="contratacoes")
     */
    private $servico;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuario", inversedBy="compras")
     */
    private $cliente;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Usuario", inversedBy="vendas")
     */
    private $freelancer;

    /**
     * @ORM\Column(type="decimal", precision=2)
     */
    private $valor;

    /**
     * @ORM\Column(type="string", length=1)
     */
    private $status;

    /**
     * @ORM\Column(type="datetime_immutable")
     * @Gedmo\Timestampable(on="create")
     */
    private $data_cadastro;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Gedmo\Timestampable(on="update")
     */
    private $data_alteracao;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $moip_cod_pedido;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $moip_cod_pagamento;

    public function getId()
    {
        return $this->id;
    }

    public function getServico(): ?Servico
    {
        return $this->servico;
    }

    public function setServico(?Servico $servico): self
    {
        $this->servico = $servico;

        return $this;
    }

    public function getCliente(): ?Usuario
    {
        return $this->cliente;
    }

    public function setCliente(?Usuario $cliente): self
    {
        $this->cliente = $cliente;

        return $this;
    }

    public function getFreelancer(): ?Usuario
    {
        return $this->freelancer;
    }

    public function setFreelancer(?Usuario $freelancer): self
    {
        $this->freelancer = $freelancer;

        return $this;
    }

    public function getValor()
    {
        return $this->valor;
    }

    public function setValor($valor): self
    {
        $this->valor = $valor;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDataCadastro(): ?\DateTimeImmutable
    {
        return $this->data_cadastro;
    }

    public function setDataCadastro(\DateTimeImmutable $data_cadastro): self
    {
        $this->data_cadastro = $data_cadastro;

        return $this;
    }

    public function getDataAlteracao(): ?\DateTimeImmutable
    {
        return $this->data_alteracao;
    }

    public function setDataAlteracao(?\DateTimeImmutable $data_alteracao): self
    {
        $this->data_alteracao = $data_alteracao;

        return $this;
    }

    public function getMoipCodPedido(): ?string
    {
        return $this->moip_cod_pedido;
    }

    public function setMoipCodPedido(?string $moip_cod_pedido): self
    {
        $this->moip_cod_pedido = $moip_cod_pedido;

        return $this;
    }

    public function getMoipCodPagamento(): ?string
    {
        return $this->moip_cod_pagamento;
    }

    public function setMoipCodPagamento(?string $moip_cod_pagamento): self
    {
        $this->moip_cod_pagamento = $moip_cod_pagamento;

        return $this;
    }
}
