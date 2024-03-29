<?php
/**
 * This file is part of the Diagla package.
 *
 * (c) Piotr Opioła <piotr@opiola.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\DTO\RegisterUserInput;
use App\StateProcessor\RegisterUserProcessor;
use App\Util\AccountRole;
use App\Util\CreatedAtTrait;
use App\Util\IdentifiableTrait;
use App\Util\StringLengthUtil;
use Carbon\CarbonImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

#[ApiResource(operations: [
    new Post(
        uriTemplate: 'register',
        status: 204,
        input: RegisterUserInput::class,
        name: 'register_user',
        processor: RegisterUserProcessor::class,
    ),
])]
#[UniqueEntity('id')]
#[Entity]
#[Table('users')]
class User implements UserInterface
{
    use IdentifiableTrait;
    use CreatedAtTrait;

    #[Length(max: StringLengthUtil::MAX)]
    #[Column(type: 'string', length: StringLengthUtil::MAX, unique: true)]
    #[Email]
    private string $username;

    #[Column(type: 'string', nullable: true)]
    private ?string $password;

    #[Column(type: 'simple_array')]
    private array $roles;

    #[Length(min: StringLengthUtil::PASSWORD_MIN, max: StringLengthUtil::PASSWORD_MAX)]
    private ?string $plainPassword = null;

    #[OneToMany(mappedBy: 'user', targetEntity: DeviceInterface::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $devices;

    public function __construct(
        string $username,
        string $plainPassword,
        ?array $roles = null,
        ?UuidInterface $id = null,
    ) {
        $this->username      = $username;
        $this->plainPassword = $plainPassword;
        $this->roles         = $roles ?? [AccountRole::ROLE_USER->value];
        $this->id            = $id ?? Uuid::uuid4();

        $this->createdAt = CarbonImmutable::now();

        $this->devices = new ArrayCollection();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = AccountRole::ROLE_USER->value;

        return array_unique($roles);
    }

    public function hasRole(AccountRole $accountRole): bool
    {
        return in_array($accountRole->value, $this->roles, true);
    }

    public function eraseCredentials(): void
    {
//        $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /* @return Collection<DeviceInterface> */
    public function getDevices(): Collection
    {
        return $this->devices;
    }

    public function addDevice(DeviceInterface $device): void
    {
        if (!$this->devices->contains($device)) {
            $this->devices->add($device);
        }
    }

    public function removeDevice(DeviceInterface $device): void
    {
        if ($this->devices->contains($device)) {
            $this->devices->removeElement($device);
        }
    }
}
