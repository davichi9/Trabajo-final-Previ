<?php

namespace App\Entity;

use App\Repository\ClientesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientesRepository::class)]
class Clientes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $apellidos = null;

    #[ORM\Column(length: 20)]
    private ?string $telefonoNumero = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $domicilio = null;

    #[ORM\OneToMany(mappedBy: 'cliente', targetEntity: Pedidos::class)]
    private Collection $pedidos;

    public function __construct()
    {
        $this->pedidos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellidos(): ?string
    {
        return $this->apellidos;
    }

    public function setApellidos(string $apellidos): static
    {
        $this->apellidos = $apellidos;

        return $this;
    }

    public function getTelefonoNumero(): ?string
    {
        return $this->telefonoNumero;
    }

    public function setTelefonoNumero(string $telefonoNumero): static
    {
        $this->telefonoNumero = $telefonoNumero;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDomicilio(): ?string
    {
        return $this->domicilio;
    }

    public function setDomicilio(string $domicilio): static
    {
        $this->domicilio = $domicilio;

        return $this;
    }

    /**
     * @return Collection<int, Pedidos>
     */
    public function getPedidos(): Collection
    {
        return $this->pedidos;
    }

    public function addPedido(Pedidos $pedido): static
    {
        if (!$this->pedidos->contains($pedido)) {
            $this->pedidos->add($pedido);
            $pedido->setCliente($this);
        }

        return $this;
    }

    public function removePedido(Pedidos $pedido): static
    {
        if ($this->pedidos->removeElement($pedido)) {
            if ($pedido->getCliente() === $this) {
                $pedido->setCliente(null);
            }
        }

        return $this;
    }
}
