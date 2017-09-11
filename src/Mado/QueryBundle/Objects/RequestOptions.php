<?php

namespace Mado\QueryBundle\Objects;

use Symfony\Component\HttpFoundation\Request;

class RequestOptions
{
    private $requestOptions;

    private function __construct(array $requestOptions)
    {
        $this->requestOptions = $requestOptions;
    }

    public static function fromRequest(Request $request)
    {
        $requestOption = [];

        $requestOption['_route'] =  $request->attributes->get('_route', []);
        $requestOption['customer_id'] =  $request->attributes->get('customer_id', []);
        $requestOption['id'] =  $request->attributes->get('id', []);
        $requestOption['filters'] = $request->query->get('filtering', []);
        $requestOption['orFiltering'] = $request->query->get('filtering_or', []);
        $requestOption['sorting '] = $request->query->get('sorting', []);
        $requestOption['printing'] = $request->query->get('printing', []);
        $requestOption['rel'] = $request->query->get('rel', []);
        $requestOption['page'] = $request->query->get('page', []);
        $requestOption['select'] = $request->query->get('select', '');
        $requestOption['filtering'] = $request->query->get('filtering', []);
        $requestOption['limit'] = $request->query->get('limit', []);

        return new self($requestOption);
    }

    public function asArray() : array
    {
        return $this->requestOptions;
    }
}