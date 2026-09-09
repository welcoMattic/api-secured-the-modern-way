<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use App\State\RateLimitedProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(security: "is_granted('ROLE_PHOTOS_READ')")]
// Une expression security sans « object » ne peut rien vérifier sur une collection :
// le filtrage par propriétaire vit donc dans App\Doctrine\PhotoOwnerExtension.
#[GetCollection(
    provider: RateLimitedProvider::class,
    openapi: new OpenApiOperation(description: "Les photos de l'utilisateur authentifié, et elles seules. Exige le rôle ROLE_PHOTOS_READ, c'est-à-dire le rôle realm PHOTOS_READ accordé via le scope photos:read. Quota par utilisateur : 100 requêtes, puis 10 par seconde ; au-delà, 429."),
)]
// L'expression d'une opération remplace celle de la ressource : sans le is_granted
// ici, le GET item ne vérifierait plus le rôle. Et l'ordre compte, « and »
// court-circuite : un appel anonyme s'arrête sur le rôle et part en 401, au lieu
// d'évaluer user.getUserIdentifier() sur null.
#[Get(
    security: "is_granted('ROLE_PHOTOS_READ') and object.owner == user.getUserIdentifier()",
    securityMessage: "Cette photo ne vous appartient pas.",
    openapi: new OpenApiOperation(description: "Une photo, à condition qu'elle appartienne à l'utilisateur authentifié. Exige le rôle ROLE_PHOTOS_READ, c'est-à-dire le rôle realm PHOTOS_READ accordé via le scope photos:read."),
)]
#[Post(
    security: "is_granted('ROLE_PHOTOS_WRITE')",
    openapi: new OpenApiOperation(description: "Dépose une photo. Exige le rôle ROLE_PHOTOS_WRITE, c'est-à-dire le rôle realm PHOTOS_WRITE accordé via le scope photos:write. Le propriétaire est imposé par le serveur depuis le token : la propriété owner n'est pas modifiable."),
)]
#[ORM\Entity]
class Photo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    public string $title = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    public string $url = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[ApiProperty(writable: false)]
    public string $owner = '';

    public function getId(): ?int
    {
        return $this->id;
    }
}
