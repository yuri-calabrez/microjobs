<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsuarioRepository")
 * @UniqueEntity("email", message="Esse e-mail já esta em uso.")
 */
class Usuario implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message="Campo nome não pode ser vazio!")
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Campo e-mail não pode ser vazio!")
     * @Assert\Email(message="Informe um e-mail válido!")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message="Informe sua senha")
     */
    private $senha;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $token;

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
     * @ORM\Column(type="json_array")
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Servico", mappedBy="usuario")
     */
    private $yes;

    public function __construct()
    {
        $this->status = false;
        $this->yes = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSenha(): ?string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): self
    {
        $this->senha = $senha;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

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

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword() : string
    {
        return $this->senha;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername() : string
    {
        return $this->email;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        return null;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles): self
    {
        $this->roles[] = $roles;
        return $this;
    }

    /**
     * @return Collection|Servico[]
     */
    public function getYes(): Collection
    {
        return $this->yes;
    }

    public function addYe(Servico $ye): self
    {
        if (!$this->yes->contains($ye)) {
            $this->yes[] = $ye;
            $ye->setUsuario($this);
        }

        return $this;
    }

    public function removeYe(Servico $ye): self
    {
        if ($this->yes->contains($ye)) {
            $this->yes->removeElement($ye);
            // set the owning side to null (unless already changed)
            if ($ye->getUsuario() === $this) {
                $ye->setUsuario(null);
            }
        }

        return $this;
    }
}
