<?php

/*
 * This file is part of the AdminBundle package.
 *
 * (c) Muhammad Surya Ihsanuddin <surya.kejawen@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfonian\Indonesia\AdminBundle\Doctrine\Orm\Filter;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Symfonian\Indonesia\AdminBundle\Contract\FieldsFilterInterface;
use Symfonian\Indonesia\AdminBundle\Contract\SoftDeletableInterface;
use Symfonian\Indonesia\AdminBundle\Grid\Filter;

/**
 * @author Muhammad Surya Ihsanuddin <surya.kejawen@gmail.com>
 */
class FieldsFilter extends SQLFilter implements FieldsFilterInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $dateTimeFormat;

    /**
     * Gets the SQL query part to add to a query.
     *
     * @param ClassMetadata $targetEntity
     * @param string $targetTableAlias
     *
     * @return string The constraint SQL if there is available, empty string otherwise.
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        $fields = array();
        $properties = $targetEntity->getReflectionProperties();
        /** @var \ReflectionProperty $property */
        foreach ($properties as $property) {
            $annotations = $this->reader->getPropertyAnnotations($property);
            foreach ($annotations as $annotation) {
                if ($annotation instanceof Filter) {
                    $fields[] = $targetEntity->getFieldName($property->getName());
                }
            }
        }

        $filter = '';
        foreach ($fields as $field) {
            if (in_array($field['type'], array('date', 'datetime', 'time'))) {
                $date = \DateTime::createFromFormat($this->dateTimeFormat, $this->getParameter('filter'));
                if ($date) {
                    $filter .= sprintf('%s.%s = %s OR', $targetTableAlias, $field['fieldName'], $date->format($this->dateTimeFormat));
                }
            } else {
                $filter .= sprintf('%s.%s LIKE %%%s% OR', $targetTableAlias, $field['fieldName'], $this->getParameter('filter'));
            }
        }

        return rtrim($filter, ' OR');
    }

    /**
     * @param Reader $reader
     */
    public function setAnnotationReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string $format
     */
    public function setDateTimeFormat($format)
    {
        $this->dateTimeFormat = $format;
    }
}
