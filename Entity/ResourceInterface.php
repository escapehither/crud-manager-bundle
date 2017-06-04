<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 26/03/17
 * Time: 19:51
 */

namespace EscapeHither\CrudManagerBundle\Entity;
use Symfony\Component\Security\Core\User\UserInterface;


interface ResourceInterface {

    public function getAuthor();
    /**
     * @param UserInterface $author
     */
    public function setAuthor(UserInterface $author);

    public function getId();
}