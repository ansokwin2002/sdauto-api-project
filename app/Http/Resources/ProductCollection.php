<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    public function toArray($request)
    {
        $isPaginated = $this->resource instanceof \Illuminate\Pagination\AbstractPaginator;

        $response = [
            'data' => ProductResource::collection($this->collection),
        ];

        if ($isPaginated) {
            $response['meta'] = [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
            ];
        }

        return $response;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'message' => 'Products retrieved successfully'
        ];
    }
}