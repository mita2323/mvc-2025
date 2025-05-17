<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a book entity.
 */
#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    /**
     * @var int The unique id for the book.
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    // @phpstan-ignore property.onlyRead
    private int $id;

    /**
     * @var string|null The title of the book.
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title cannot be empty")]
    private ?string $title = null;

    /**
     * @var string|null The ISBN of the book.
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "ISBN cannot be empty")]
    private ?string $isbn = null;

    /**
     * @var string|null The author of the book.
     */
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Author cannot be empty")]
    private ?string $author = null;

    /**
     * @var string|null The URL to the book's image.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url(message: "The image must be a valid URL")]
    private ?string $imageUrl = null;

    /**
     * Gets the book's unique id.
     * @return int|null The book ID, or null.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Gets the book's title.
     * @return string|null The title, or null.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Sets the book's title.
     * @param string $title The title to set.
     * @return static Returns this instance.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Gets the book's ISBN.
     * @return string|null The ISBN, or null.
     */
    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    /**
     * Sets the book's ISBN.
     * @param string $isbn The ISBN to set.
     * @return static Returns this instance.
     */
    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;
        return $this;
    }

    /**
     * Gets the book's author.
     * @return string|null The author, or null.
     */
    public function getAuthor(): ?string
    {
        return $this->author;
    }

    /**
     * Sets the book's author.
     * @param string $author The author to set.
     * @return static Returns this instance.
     */
    public function setAuthor(string $author): static
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Gets the book's image URL.
     * @return string|null The image URL, or null.
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * Sets the book's image URL.
     * @param string|null $imageUrl The image to set, or null.
     * @return static Returns this instance.
     */
    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }
}
