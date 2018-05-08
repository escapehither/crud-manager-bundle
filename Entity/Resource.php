<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Resource.
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class Resource implements ResourceInterface
{
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
    public function getAuthor()
    {
        return $this->author;
    }

    /**
    * @param UserInterface $author
    */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;
    }

    /**
     * Get the created time
     *
     * @return \DateTime $created The created time
     */
    public function getCreated()
    {
        return $this->created;
    }

     /**
     * Get the updated time
     *
     * @return \DateTime $updated The updated time
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get the content changed time
     *
     * @return \DateTime $contentChanged The content changed time
     */
    public function getContentChanged()
    {
        return $this->contentChanged;
    }

    /**
     * Get the published time
     *
     * @return \DateTime $published The published time
     */
    public function getPublished()
    {
        return $this->published;
    }
  /**
   * toString
   *
   * @return string
   */
    public function __toString()
    {
        return $this->getName();
    }
}
