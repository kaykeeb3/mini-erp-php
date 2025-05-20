<?php
namespace App\Core;

class Router
{
    private $routes = [];

    public function get($uri, $action)
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function post($uri, $action)
    {
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    public function put($uri, $action)
    {
        $this->routes['PUT'][$this->normalize($uri)] = $action;
    }

    public function delete($uri, $action)
    {
        $this->routes['DELETE'][$this->normalize($uri)] = $action;
    }

    public function dispatch($method, $uri)
    {
        $uri = $this->normalize($uri);
        $method = strtoupper($method);

        foreach ($this->routes[$method] ?? [] as $route => $action) {
            $pattern = preg_replace('#\{[^\}]+\}#', '([^/]+)', $route);
            $pattern = "#^" . $pattern . "$#";

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove o match completo, ficam só os parâmetros

                // Se $action for array [ControllerClass, method]
                if (is_array($action)) {
                    $controllerClass = $action[0];
                    $methodName = $action[1];
                    $controllerInstance = new $controllerClass();
                    return call_user_func_array([$controllerInstance, $methodName], $matches);
                }

                // Se for callable direto (ex: closure)
                if (is_callable($action)) {
                    return call_user_func_array($action, $matches);
                }

                // Se não for nenhum dos acima, erro
                throw new \Exception("Invalid route action");
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found']);
    }

    private function normalize($uri)
    {
        return rtrim($uri, '/') ?: '/';
    }
}
