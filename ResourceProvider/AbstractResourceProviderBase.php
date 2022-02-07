<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace EscapeHither\CrudManagerBundle\ResourceProvider;

/**
 * Resource provider Base
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
abstract class AbstractResourceProviderBase
{

    /**
     * Add link
     *
     * @param string $ref The reference
     * @param string $url The resource page url
     */
    protected function addLink($ref, $url)
    {
        $this->links[$ref] = $url;
    }

    /**
     * Get the properties Of your entity.
     *
     * @param string $resourceClass The resource Class.
     * @return array
     */
    protected function getEntityPropertiesName($resourceClass)
    {
        $resourceEntityReflector = new \ReflectionClass($resourceClass);
        $properties = $resourceEntityReflector->getProperties();
        $entityPropertiesName = [];

        foreach ($properties as $property) {
            $entityPropertiesName[] = $property->getName();
        }

        return $entityPropertiesName;
    }
    // TODO cleaning
}
