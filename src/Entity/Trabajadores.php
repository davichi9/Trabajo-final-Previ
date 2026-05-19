<?php

namespace App\Entity;

use App\Repository\TrabajadoresRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TrabajadoresRepository::class)]
class Trabajadores
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
    private ?string $contraseña = null;

    #[ORM\Column(length: 100)]
    private ?string $rol = null;

    #[ORM\Column(type: 'boolean')]
    private bool $activo = true;

    #[ORM\OneToMany(targetEntity: Pedidos::class, mappedBy: 'trabajador')]
    private Collection $pedidosAsignados;

    public function __construct()
    {
        $this->pedidosAsignados = new ArrayCollection();
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

    public function getContraseña(): ?string
    {
        return $this->contraseña;
    }

    public function setContraseña(string $contraseña): static
    {
        $this->contraseña = $contraseña;

        return $this;
    }

    public function getRol(): ?string
    {
        return $this->rol;
    }

    public function setRol(string $rol): static
    {
        $this->rol = $rol;

        return $this;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return Collection<int, Pedidos>
     */
    public function getPedidosAsignados(): Collection
    {
        return $this->pedidosAsignados;
    }

    public function addPedidosAsignado(Pedidos $pedidosAsignado): static
    {
        if (!$this->pedidosAsignados->contains($pedidosAsignado)) {
            $this->pedidosAsignados->add($pedidosAsignado);
            $pedidosAsignado->setTrabajador($this);
        }

        return $this;
    }

    public function removePedidosAsignado(Pedidos $pedidosAsignado): static
    {
        if ($this->pedidosAsignados->removeElement($pedidosAsignado)) {
            if ($pedidosAsignado->getTrabajador() === $this) {
                $pedidosAsignado->setTrabajador(null);
            }
        }

        return $this;
    }
}
