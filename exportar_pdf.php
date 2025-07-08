<?php
require_once __DIR__ . '/vendor/autoload.php';
include("conexao.php");

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'orientation' => 'P',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 25,
    'margin_bottom' => 20,
    'margin_header' => 10,
    'margin_footer' => 10
]);

// CSS para estilizar o PDF
$stylesheet = '
<style>
    body {
        font-family: "Roboto", sans-serif;
        color: #333;
    }
    h1 {
        color: #1abc9c;
        text-align: center;
        margin-bottom: 20px;
    }
    h2 {
        color: #2c3e50;
        border-left: 4px solid #1abc9c;
        padding-left: 10px;
        margin-top: 30px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        margin-bottom: 20px;
    }
    th {
        background-color: #1abc9c;
        color: white;
        padding: 8px;
        text-align: left;
    }
    td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }
    tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .summary-box {
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
    }
    .summary-title {
        font-weight: bold;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    .summary-value {
        font-size: 18px;
        color: #1abc9c;
    }
    .footer {
        text-align: center;
        font-size: 10px;
        color: #777;
        margin-top: 20px;
    }
</style>
';

// Início do HTML
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Relatório Completo - Gestão de Estoque</title>
</head>
<body>
    '.$stylesheet.'
    <h1>Relatório Completo - Gestão de Estoque</h1>
    
    <div style="text-align: center; margin-bottom: 20px;">
        <small>Gerado em: '.date('d/m/Y H:i:s').'</small>
    </div>
';

// Resumo Geral
$html .= '
    <div class="summary-box">
        <div class="summary-title">Resumo Geral</div>
';

// Total de itens no estoque
$sql_total_itens = "SELECT SUM(quantidade_restante) as total FROM estoques";
$result_total_itens = $conn->query($sql_total_itens);
$total_itens = $result_total_itens->fetch_assoc()['total'];

// Valor total do estoque
$sql_valor_estoque = "SELECT SUM(a.preco * e.quantidade_restante) as total 
                      FROM estoques e 
                      JOIN alimentos a ON e.alimento_id = a.id";
$result_valor_estoque = $conn->query($sql_valor_estoque);
$valor_estoque = $result_valor_estoque->fetch_assoc()['total'];

// Total de vendas
$sql_total_vendas = "SELECT SUM(quantidade_vendida) as total FROM vendas";
$result_total_vendas = $conn->query($sql_total_vendas);
$total_vendas = $result_total_vendas->fetch_assoc()['total'];

$html .= '
        <table width="100%">
            <tr>
                <td width="33%"><strong>Total de Itens:</strong> <span class="summary-value">'.$total_itens.'</span></td>
                <td width="33%"><strong>Valor do Estoque:</strong> <span class="summary-value">R$ '.number_format($valor_estoque, 2, ',', '.').'</span></td>
                <td width="33%"><strong>Total Vendido:</strong> <span class="summary-value">'.$total_vendas.' unidades</span></td>
            </tr>
        </table>
    </div>
';

// Estoque Atual
$html .= '
    <h2>Estoque Atual</h2>
    <table>
        <thead>
            <tr>
                <th>Alimento</th>
                <th>Preço/unidade</th>
                <th>Quantidade</th>
                <th>Valor Total</th>
                <th>Última Atualização</th>
            </tr>
        </thead>
        <tbody>
';

$sql_estoque = "SELECT a.nome, a.preco, e.quantidade_restante, e.data_atualizacao 
                FROM estoques e 
                JOIN alimentos a ON e.alimento_id = a.id 
                ORDER BY e.data_atualizacao DESC";
$result_estoque = $conn->query($sql_estoque);

if ($result_estoque->num_rows > 0) {
    while($row = $result_estoque->fetch_assoc()) {
        $valor_total = $row['preco'] * $row['quantidade_restante'];
        $html .= "<tr>
                    <td>{$row['nome']}</td>
                    <td>R$ ".number_format($row['preco'], 2, ',', '.')."</td>
                    <td>{$row['quantidade_restante']}</td>
                    <td>R$ ".number_format($valor_total, 2, ',', '.')."</td>
                    <td>".date('d/m/Y', strtotime($row['data_atualizacao']))."</td>
                </tr>";
    }
} else {
    $html .= "<tr><td colspan='5'>Nenhum dado de estoque disponível</td></tr>";
}

$html .= '
        </tbody>
    </table>
';

// Últimas Vendas
$html .= '
    <h2>Últimas Vendas</h2>
    <table>
        <thead>
            <tr>
                <th>Alimento</th>
                <th>Quantidade</th>
                <th>Data</th>
                <th>Valor Total</th>
            </tr>
        </thead>
        <tbody>
';

$sql_vendas = "SELECT v.quantidade_vendida, v.data_venda, a.nome, a.preco 
               FROM vendas v 
               JOIN alimentos a ON v.alimento_id = a.id 
               ORDER BY v.data_venda DESC 
               LIMIT 15";
$result_vendas = $conn->query($sql_vendas);

if ($result_vendas->num_rows > 0) {
    while($row = $result_vendas->fetch_assoc()) {
        $valor_total = $row['preco'] * $row['quantidade_vendida'];
        $html .= "<tr>
                    <td>{$row['nome']}</td>
                    <td>{$row['quantidade_vendida']}</td>
                    <td>".date('d/m/Y', strtotime($row['data_venda']))."</td>
                    <td>R$ ".number_format($valor_total, 2, ',', '.')."</td>
                </tr>";
    }
} else {
    $html .= "<tr><td colspan='4'>Nenhuma venda registrada</td></tr>";
}

$html .= '
        </tbody>
    </table>
';

// Rodapé
$html .= '
    <div class="footer">
        Relatório gerado automaticamente pelo Sistema de Gestão de Estoque
    </div>
</body>
</html>
';

// Gerar PDF
$mpdf->WriteHTML($html);
$mpdf->Output('relatorio_estoque_completo.pdf', 'D');

$conn->close();
?>