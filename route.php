<?php

$clientURL = $_SERVER['HTTP_ORIGIN'];
// Verifica se l'origine della richiesta corrisponde all'URL del tuo client
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Abilita CORS solo per l'URL del tuo client
    header("Access-Control-Allow-Origin: $clientURL");
    header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Rispondi con uno stato 200 OK
    http_response_code(200);
    exit;
}

require_once('./DbManager/Product.php');

$routes = [
    'GET' => [],
    'POST' => [],
    'PATCH' => [],
    'DELETE' => []
];


function addRoute($method, $path, $callback)
{
    global $routes;
    $routes[$method][$path] = $callback;
}


function getRequestMethod()
{
    return $_SERVER['REQUEST_METHOD'];
}


function getRequestPath()
{
    $path = $_SERVER['REQUEST_URI'];
    $path = parse_url($path, PHP_URL_PATH);
    return rtrim($path, '/');
}




function handleRequest()
{
    global $routes;
    $method = getRequestMethod();
    $path = getRequestPath();

    if (!empty($routes[$method])) {
        foreach ($routes[$method] as $routePath => $callback) {
            if (preg_match('#^' . $routePath . '$#', $path, $matches)) {
                call_user_func_array($callback, $matches);
                return;
            }
        }
    } else {
        http_response_code(404);
        echo "404 Not Found";
    }
}

// Funzione per impostare le intestazioni CORS

addRoute('GET', '/products/(\d+)', function ($matches) {
    $parts = explode('/', $matches);
    $id = end($parts);
    $product = Product::Find($id);
    header("Location: /products/" . $id);
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/vnd.api+json');
    if ($product) {
        $data =
            [
                'type' => 'products',
                'id' => $product->getId(),
                'attributes' =>
                    [
                        'nome' => $product->getNome(),
                        'prezzo' => $product->getPrezzo(),
                        'marca' => $product->getMarca()
                    ]

            ];
        echo json_encode(['data' => $data], JSON_PRETTY_PRINT);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
});

addRoute('GET', '/products', function () {
    $products = Product::FetchAll();
    foreach ($products as $product) {
        $data[]= [
            'type' => 'products',
            'id' => $product->getId(),
            'attributes' =>
                [
                    'nome' => $product->getNome(),
                    'prezzo' => $product->getPrezzo(),
                    'marca' => $product->getMarca()
                ]
        ];

    }
    header('Location: /products');
    header('HTTP/1.1 200 Ok');
    header('Content-Type: application/vnd.api+json');
    echo json_encode(['data' => $data], JSON_PRETTY_PRINT);


});

addRoute('DELETE', '/products/(\d+)', function ($matches) {
    $parts = explode('/', $matches);
    $id = end($parts);
    $product = Product::Find($id);
    if ($product) {
        if ($product->Delete()) {
            http_response_code(204);
        } else {
            header("Location: /products/{$product->getId()}");
            header('Content-Type: application/vnd.api+json');
            http_response_code(500);
            echo json_encode(['error' => 'Errore durante l\'eliminazione del prodotto']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Prodotto non trovato']);
    }
});

addRoute('POST', '/products', function () {
    if (isset($_POST['data']))
        $postData = $_POST;
    else
        $postData = json_decode(file_get_contents("php://input"), true);
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($postData['data']['attributes']['marca'], $postData['data']['attributes']['nome'], $postData['data']['attributes']['prezzo'])) {
            $newPrd = Product::Create($postData["data"]["attributes"]);
            $data = [
                'type' => 'products',
                'id' => $newPrd->getId(),
                'attributes' =>
                    [
                        'nome' => $newPrd->getNome(),
                        'marca' => $newPrd->getMarca(),
                        'prezzo' => $newPrd->getPrezzo()
                    ]
            ];
            $response = ['data' => $data];
            echo json_encode($response, JSON_PRETTY_PRINT);
            header("Location: /products/{$newPrd->getId()}");
            header('HTTP/1.1 201 CREATED');
            http_response_code(201);
            header('Content-Type: application/vnd.api+json');
        } else {
            http_response_code(500);
        }
    } catch (PDOException $e) {
        header("Location: /products");
        header('HTTP/1.1 500 INTERNAL SERVER ERROR');
        header('Content-Type: application/vnd.api+json');
        http_response_code(500);
        echo json_encode(['error' => 'Errore nella creazione del prodotto']);
    }
});

addRoute('PATCH', '/products/(\d+)', function ($matches) {
    $parts = explode('/', $matches);
    $id = end($parts);
    $patch = json_decode(file_get_contents("php://input"), true);
    $product = Product::Find($id);

    try {
        if ($patch && $product) {
            $newPrd = $product->Update($patch["data"]["attributes"]);

            $data = [
                'type' => 'products',
                'id' => $newPrd->getId(),
                'attributes' => [
                    'nome' => $newPrd->getNome(),
                    'marca' => $newPrd->getMarca(),
                    'prezzo' => $newPrd->getPrezzo()
                ]
            ];
            $response = ['data' => $data];
            header("Location: /products/" . $id);
            header('HTTP/1.1 200 OK');
            header('Content-Type: application/vnd.api+json');
            echo json_encode($response, JSON_PRETTY_PRINT);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Prodotto non trovato']);
        }
    } catch (PDOException $e) {
        header("Location: /products/" . $id);
        header('HTTP/1.1 500 INTERNAL SERVER ERROR');
        header('Content-Type: application/vnd.api+json');
        http_response_code(500);
        echo json_encode(['error' => 'Errore nell aggiornamento del prodotto']);
    }
});

try {
    handleRequest();
} catch (Exception $e) {
    echo json_encode(["Error" => $e], JSON_PRETTY_PRINT);
}
exit;