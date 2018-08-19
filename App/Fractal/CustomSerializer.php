<?php
namespace App\Fractal;

use League\Fractal\Serializer\ArraySerializer;

/**
 * Class CustomSerializer
 * @package App\Fractal
 * 数据格式转换
 */
class CustomSerializer extends ArraySerializer
{
    public function collection($resourceKey, array $data)
    {
        if ($resourceKey !== null) {
            return [$resourceKey => $data];
        }
        return ['data' => $data];
    }

    public function item($resourceKey, array $data)
    {
        if ($resourceKey !== null) {
            return [$resourceKey => $data];
        }
        return ['item' => $data];
    }
}