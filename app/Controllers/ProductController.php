<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Variation;
use App\Models\Stock;

class ProductController
{
    public static function index()
    {
        $products = Product::all();

        foreach ($products as &$product) {
            $variations = Variation::getByProduct($product['id']);
            foreach ($variations as &$variation) {
                $variation['stock'] = Stock::getByVariation($variation['id']);
            }
            $product['variations'] = $variations;
        }

        header('Content-Type: application/json');
        echo json_encode($products);
    }

    public static function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        $variations = Variation::getByProduct($id);
        foreach ($variations as &$variation) {
            $variation['stock'] = Stock::getByVariation($variation['id']);
        }

        $product['variations'] = $variations;

        header('Content-Type: application/json');
        echo json_encode($product);
    }

    public static function store()
    {
        header('Content-Type: application/json');
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['name'], $data['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and price are required']);
            return;
        }

        $productId = Product::create($data);

        if (isset($data['variations']) && is_array($data['variations'])) {
            foreach ($data['variations'] as $variationData) {
                if (isset($variationData['name'], $variationData['stock'])) {
                    $variationId = Variation::create($productId, $variationData['name']);
                    Stock::create($variationId, $variationData['stock']);
                }
            }
        }

        echo json_encode(['message' => 'Product created', 'id' => $productId]);
    }

    public static function update($id)
    {
        header('Content-Type: application/json');
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['name'], $data['price'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Name and price are required']);
            return;
        }

        if (!Product::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        Product::update($id, $data);

        if (isset($data['variations']) && is_array($data['variations'])) {
            Variation::deleteByProduct($id);
            foreach ($data['variations'] as $variationData) {
                if (isset($variationData['name'], $variationData['stock'])) {
                    $variationId = Variation::create($id, $variationData['name']);
                    Stock::create($variationId, $variationData['stock']);
                }
            }
        }

        echo json_encode(['message' => 'Product updated']);
    }

    public static function delete($id)
    {
        header('Content-Type: application/json');

        if (!Product::find($id)) {
            http_response_code(404);
            echo json_encode(['error' => 'Product not found']);
            return;
        }

        Variation::deleteByProduct($id);
        Product::delete($id);

        echo json_encode(['message' => 'Product deleted']);
    }
}
