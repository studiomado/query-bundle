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
        $operator  = key($rawIds);
        $ids       = join(',', current($rawIds));
        $path      = $params['path'];

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

    public function getRawFilter()
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
        return new self([
            'raw_filter' => self::buildRawFilter($path, $this->operator),
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
}
