<?php
// Arquivo principal: index.php

require 'vendor/autoload.php';

use Slim\Factory\AppFactory;
use PDO;

// Inicializar a aplicação Slim
$app = AppFactory::create();

// Configurar conexão com o banco SQLite
function getDatabaseConnection()
{
    $pdo = new PDO('sqlite:sistema_saude.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// Criar tabelas no banco de dados (executar uma vez)
$app->get('/setup', function ($request, $response) {
    $db = getDatabaseConnection();

    // Tabela de pacientes
    $db->exec("CREATE TABLE IF NOT EXISTS pacientes (
        id INTEGER PRIMARY KEY,
        numero_atendimento TEXT UNIQUE,
        nome_completo TEXT,
        sexo TEXT,
        email TEXT,
        celular TEXT
    )");

    // Tabela de exames
    $db->exec("CREATE TABLE IF NOT EXISTS exames (
        id INTEGER PRIMARY KEY,
        codigo TEXT UNIQUE,
        descricao TEXT,
        valor REAL
    )");

    // Tabela de relação paciente-exames
    $db->exec("CREATE TABLE IF NOT EXISTS paciente_exames (
        id INTEGER PRIMARY KEY,
        paciente_id INTEGER,
        exame_id INTEGER,
        FOREIGN KEY(paciente_id) REFERENCES pacientes(id),
        FOREIGN KEY(exame_id) REFERENCES exames(id)
    )");

    $response->getBody()->write("Tabelas criadas com sucesso.");
    return $response;
});

// Rota para cadastrar um paciente
$app->post('/pacientes', function ($request, $response) {
    $data = $request->getParsedBody();

    $numeroAtendimento = uniqid(); // Gerar número aleatório único
    $db = getDatabaseConnection();

    $stmt = $db->prepare("INSERT INTO pacientes (numero_atendimento, nome_completo, sexo, email, celular) 
                           VALUES (:numero_atendimento, :nome_completo, :sexo, :email, :celular)");
    $stmt->execute([
        ':numero_atendimento' => $numeroAtendimento,
        ':nome_completo' => $data['nome_completo'],
        ':sexo' => $data['sexo'],
        ':email' => $data['email'],
        ':celular' => $data['celular'],
    ]);

    $response->getBody()->write(json_encode(['status' => 'success', 'numero_atendimento' => $numeroAtendimento]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Rota para cadastrar um exame
$app->post('/exames', function ($request, $response) {
    $data = $request->getParsedBody();

    $db = getDatabaseConnection();

    try {
        $stmt = $db->prepare("INSERT INTO exames (codigo, descricao, valor) VALUES (:codigo, :descricao, :valor)");
        $stmt->execute([
            ':codigo' => $data['codigo'],
            ':descricao' => $data['descricao'],
            ':valor' => $data['valor'],
        ]);
        $response->getBody()->write(json_encode(['status' => 'success']));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
    }

    return $response->withHeader('Content-Type', 'application/json');
});

// Rota para vincular exames a um paciente
$app->post('/pacientes/{id}/exames', function ($request, $response, $args) {
    $pacienteId = $args['id'];
    $data = $request->getParsedBody();

    $db = getDatabaseConnection();

    foreach ($data['exames'] as $codigoExame) {
        $stmt = $db->prepare("SELECT id FROM exames WHERE codigo = :codigo");
        $stmt->execute([':codigo' => $codigoExame]);
        $exame = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($exame) {
            $stmt = $db->prepare("INSERT INTO paciente_exames (paciente_id, exame_id) VALUES (:paciente_id, :exame_id)");
            $stmt->execute([
                ':paciente_id' => $pacienteId,
                ':exame_id' => $exame['id'],
            ]);
        }
    }

    $response->getBody()->write(json_encode(['status' => 'success']));
    return $response->withHeader('Content-Type', 'application/json');
});

// Rota para gerar relatório do paciente
$app->get('/pacientes/{id}/relatorio', function ($request, $response, $args) {
    $pacienteId = $args['id'];
    $db = getDatabaseConnection();

    $stmt = $db->prepare("SELECT * FROM pacientes WHERE id = :id");
    $stmt->execute([':id' => $pacienteId]);
    $paciente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paciente) {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Paciente não encontrado.']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    $stmt = $db->prepare("SELECT exames.codigo, exames.descricao, exames.valor 
                           FROM paciente_exames
                           JOIN exames ON exames.id = paciente_exames.exame_id
                           WHERE paciente_exames.paciente_id = :paciente_id");
    $stmt->execute([':paciente_id' => $pacienteId]);
    $exames = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $paciente['exames'] = $exames;

    $response->getBody()->write(json_encode($paciente));
    return $response->withHeader('Content-Type', 'application/json');
});

// Iniciar a aplicação
$app->run();
