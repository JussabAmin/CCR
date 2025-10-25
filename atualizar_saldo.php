<?php
require_once "seguranca.php";
require_once "config.php";

// A senha especial para permitir a alteração
// SUBSTITUA 'SUA_SENHA_ESPECIAL' PELA SUA SENHA REAL
$senha_especial = 'sonhodourado';

header('Content-Type: application/json');

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo'] ?? '';
    $novo_saldo = $_POST['novo_saldo'] ?? 0;
    $senha_digitada = $_POST['senha_especial'] ?? '';
    $id_usuario = $_SESSION["id"];

    // 1. Verifica se a senha está correta
    if ($senha_digitada !== $senha_especial) {
        echo json_encode(['success' => false, 'message' => 'Senha especial incorreta.']);
        exit;
    }

    // 2. Busca o saldo atual do tipo para calcular a diferença
    $tabela = '';
    $coluna_origem_destino = '';

    if ($tipo === 'bancos' || $tipo === 'caixa_geral') {
        $tabela = 'ativos';
        $coluna_origem_destino = 'origem';
    } else {
        echo json_encode(['success' => false, 'message' => 'Tipo de saldo inválido.']);
        exit;
    }

    $query_saldo_atual = "SELECT SUM(valor) as saldo FROM $tabela WHERE usuario_id = ? AND $coluna_origem_destino = ?";
    if ($stmt = mysqli_prepare($link, $query_saldo_atual)) {
        mysqli_stmt_bind_param($stmt, "is", $id_usuario, $tipo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        $saldo_atual = $row['saldo'] ?? 0;
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro na consulta do saldo.']);
        exit;
    }

    // 3. Calcula a diferença e insere uma nova transação
    $diferenca = $novo_saldo - $saldo_atual;
    $descricao = 'Ajuste de Saldo';
    $nova_tabela = ($diferenca > 0) ? 'ativos' : 'despesas';
    $coluna_tipo = ($diferenca > 0) ? 'origem' : 'categoria';

    $query_ajuste = "INSERT INTO $nova_tabela (usuario_id, data, descricao, $coluna_tipo, valor) VALUES (?, NOW(), ?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $query_ajuste)) {
        $valor_ajuste = abs($diferenca);
        mysqli_stmt_bind_param($stmt, "isss", $id_usuario, $descricao, $tipo, $valor_ajuste);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            echo json_encode(['success' => true, 'message' => 'Saldo atualizado com sucesso!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nenhuma alteração no saldo.']);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao inserir ajuste.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de requisição inválido.']);
}

mysqli_close($link);
?>