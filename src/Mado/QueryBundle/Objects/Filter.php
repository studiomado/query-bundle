<?php

namespace Mado\QueryBundle\Objects;

class Filter
{
    private $rawFilter;

    private $ids;

    private $operator;

    public static function box(array $params)
    {
        $rawIds    = $params['ids'];
        $path      = $params['path'];

        $operator  = key($rawIds);
        $ids       = join(',', current($rawIds));

        return new self([
            'raw_filter' => self::buildRawFilter($path, $operator),
            'ids'        => $ids,
            'operator'   => $operator,
            'path'       => $path,
        ]);
    }

    private static function buildRawFilter($path, $operator)
    {
        return $path . '.id|' . $operator;
    }

    private function __construct(array $params)
    {
        $this->rawFilter = $params['raw_filter'];
        $this->ids       = $params['ids'];
        $this->operator  = $params['operator'];
        $this->path      = $params['path'];
    }

    public function getFieldAndOperator()
    {
        return $this->rawFilter;
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function withPath($path)
    {
        $rawFilter = self::buildRawFilter($path, $this->operator);

        if ($path == '') {
            $rawFilter = str_replace('.', '', $rawFilter);
        }

        return new self([
            'raw_filter' => $rawFilter,
            'ids'        => $this->ids,
            'operator'   => $this->operator,
            'path'       => $path,
        ]);
    }

    public function withFullPath($path)
    {
        $explodedPath = explode('|', $path);

        $path = $explodedPath[0];
        $operator = $explodedPath[1];

        return new self([
            'raw_filter' => join('|', $explodedPath),
            'ids'        => $this->ids,
            'operator'   => $operator,
            'path'       => $path,
        ]);
    }

    public function getField()
    {
        $explodedPath = explode('|', $this->getFieldAndOperator());

        $field = $explodedPath[0];

        return $field;
    }

    public static function fromQueryStringFilter(array $params)
    {
        return new self([
            'raw_filter' => key($params),
            'ids' => current($params),
            'operator' => explode('|', key($params))[1],
            'path' => null,
        ]);
    }

    public function getValue()
    {
        return $this->ids;
    }
}
