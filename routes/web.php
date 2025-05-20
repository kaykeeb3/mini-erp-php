<?php

use App\Controllers\CartController;
use App\Controllers\CouponController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use App\Controllers\WebhookController;

// Produtos
$router->get('/api/products', [ProductController::class, 'index']);
$router->get('/api/products/{id}', [ProductController::class, 'show']);
$router->post('/api/products', [ProductController::class, 'store']);
$router->put('/api/products/{id}', [ProductController::class, 'update']);
$router->delete('/api/products/{id}', [ProductController::class, 'destroy']);

// Carrinho
$router->post('/api/cart/add', [CartController::class, 'add']);
$router->get('/api/cart', [CartController::class, 'show']);

// Cupons
$router->post('/api/coupons', [CouponController::class, 'store']);
$router->get('/api/coupons', [CouponController::class, 'index']);
$router->post('/api/coupons/validate', [CouponController::class, 'validate']);


// Pedido
$router->post('/api/order/checkout', [OrderController::class, 'checkout']);
$router->get('/api/cart/fake', [OrderController::class, 'addFakeCart']);

// Webhook
$router->post('/api/webhook/receive', [WebhookController::class, 'statusUpdate']);
