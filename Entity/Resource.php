<?php
namespace StarterKit\CrudBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Georden Gaël Louzayadio <glouz@gania.com>
 */

/**
 * Resource.
 */
class Resource implements ResourceInterface {
  /**
   * @var \DateTime $created
   */
  protected $created;

  /**
   * @var \DateTime $updated
   */
  protected $updated;

  /**
   * @var \DateTime $contentChanged
   */
  protected $published;

  /**
   * @var \DateTime $contentChanged
   */
  protected $contentChanged;

  /**
   * @var UserInterface
   */
  protected $author;

  /**
   * @return UserInterface
   */
  public function getAuthor() {
    return $this->author;
  }

  /**
   * @param UserInterface $author
   */
  public function setAuthor(UserInterface $author) {
    $this->author = $author;
  }

  public function getCreated()
  {
    return $this->created;
  }

  public function getUpdated()
  {
    return $this->updated;
  }

  public function getContentChanged()
  {
    return $this->contentChanged;
  }

  public function getPublished()
  {
    return $this->published;
  }
  /**
   * toString
   * @return string
   */
  public function __toString()
  {
    return $this->getName();
  }

  public function getId() {
    // TODO: Implement getId() method.
  }
  public function getName() {

  }


}