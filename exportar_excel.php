<?php
include("conexao.php");

// Criar cabeçalho para download do arquivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="relatorio_estoque.xlsx"');
header('Cache-Control: max-age=0');

// Incluir a biblioteca PhpSpreadsheet
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Criar uma nova planilha
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Estilos para a planilha
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1ABC9C']
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
        ]
    ]
];

$titleStyle = [
    'font' => [
        'bold' => true,
        'size' => 14,
        'color' => ['rgb' => '1ABC9C']
    ]
];

$summaryStyle = [
    'font' => [
        'bold' => true
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'F8F9FA']
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
        ]
    ]
];

// Título do relatório
$sheet->setCellValue('A1', 'Relatório Completo - Gestão de Estoque');
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1')->applyFromArray($titleStyle);

// Data de geração
$sheet->setCellValue('A2', 'Gerado em: ' . date('d/m/Y H:i:s'));
$sheet->mergeCells('A2:E2');

// Resumo Geral
$sheet->setCellValue('A4', 'RESUMO GERAL');
$sheet->mergeCells('A4:E4');
$sheet->getStyle('A4')->applyFromArray($summaryStyle);

// Consultas para o resumo
$sql_total_itens = "SELECT SUM(quantidade_restante) as total FROM estoques";
$result_total_itens = $conn->query($sql_total_itens);
$total_itens = $result_total_itens->fetch_assoc()['total'];

$sql_valor_estoque = "SELECT SUM(a.preco * e.quantidade_restante) as total 
                      FROM estoques e 
                      JOIN alimentos a ON e.alimento_id = a.id";
$result_valor_estoque = $conn->query($sql_valor_estoque);
$valor_estoque = $result_valor_estoque->fetch_assoc()['total'];

$sql_total_vendas = "SELECT SUM(quantidade_vendida) as total FROM vendas";
$result_total_vendas = $conn->query($sql_total_vendas);
$total_vendas = $result_total_vendas->fetch_assoc()['total'];

// Preencher resumo
$sheet->setCellValue('A5', 'Total de Itens no Estoque:');
$sheet->setCellValue('B5', $total_itens);

$sheet->setCellValue('A6', 'Valor Total do Estoque:');
$sheet->setCellValue('B6', 'R$ ' . number_format($valor_estoque, 2, ',', '.'));

$sheet->setCellValue('A7', 'Total de Unidades Vendidas:');
$sheet->setCellValue('B7', $total_vendas);

// Estoque Atual
$sheet->setCellValue('A9', 'ESTOQUE ATUAL');
$sheet->mergeCells('A9:E9');
$sheet->getStyle('A9')->applyFromArray($summaryStyle);

// Cabeçalho da tabela de estoque
$sheet->setCellValue('A10', 'Alimento');
$sheet->setCellValue('B10', 'Preço/unidade');
$sheet->setCellValue('C10', 'Quantidade');
$sheet->setCellValue('D10', 'Valor Total');
$sheet->setCellValue('E10', 'Última Atualização');
$sheet->getStyle('A10:E10')->applyFromArray($headerStyle);

// Consulta e preenchimento dos dados de estoque
$sql_estoque = "SELECT a.nome, a.preco, e.quantidade_restante, e.data_atualizacao 
                FROM estoques e 
                JOIN alimentos a ON e.alimento_id = a.id 
                ORDER BY e.data_atualizacao DESC";
$result_estoque = $conn->query($sql_estoque);

$row = 11;
if ($result_estoque->num_rows > 0) {
    while($data = $result_estoque->fetch_assoc()) {
        $valor_total = $data['preco'] * $data['quantidade_restante'];
        
        $sheet->setCellValue('A'.$row, $data['nome']);
        $sheet->setCellValue('B'.$row, $data['preco']);
        $sheet->getStyle('B'.$row)->getNumberFormat()->setFormatCode('"R$ "#,##0.00');
        $sheet->setCellValue('C'.$row, $data['quantidade_restante']);
        $sheet->setCellValue('D'.$row, $valor_total);
        $sheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('"R$ "#,##0.00');
        $sheet->setCellValue('E'.$row, date('d/m/Y', strtotime($data['data_atualizacao'])));
        
        $row++;
    }
} else {
    $sheet->setCellValue('A'.$row, 'Nenhum dado de estoque disponível');
    $sheet->mergeCells('A'.$row.':E'.$row);
}

// Últimas Vendas
$row += 2; // Espaçamento
$sheet->setCellValue('A'.$row, 'ÚLTIMAS VENDAS');
$sheet->mergeCells('A'.$row.':E'.$row);
$sheet->getStyle('A'.$row)->applyFromArray($summaryStyle);

$row++;
$sheet->setCellValue('A'.$row, 'Alimento');
$sheet->setCellValue('B'.$row, 'Quantidade');
$sheet->setCellValue('C'.$row, 'Preço Unitário');
$sheet->setCellValue('D'.$row, 'Valor Total');
$sheet->setCellValue('E'.$row, 'Data da Venda');
$sheet->getStyle('A'.$row.':E'.$row)->applyFromArray($headerStyle);

// Consulta e preenchimento dos dados de vendas
$sql_vendas = "SELECT v.quantidade_vendida, v.data_venda, a.nome, a.preco 
               FROM vendas v 
               JOIN alimentos a ON v.alimento_id = a.id 
               ORDER BY v.data_venda DESC 
               LIMIT 15";
$result_vendas = $conn->query($sql_vendas);

$row++;
if ($result_vendas->num_rows > 0) {
    while($data = $result_vendas->fetch_assoc()) {
        $valor_total = $data['preco'] * $data['quantidade_vendida'];
        
        $sheet->setCellValue('A'.$row, $data['nome']);
        $sheet->setCellValue('B'.$row, $data['quantidade_vendida']);
        $sheet->setCellValue('C'.$row, $data['preco']);
        $sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode('"R$ "#,##0.00');
        $sheet->setCellValue('D'.$row, $valor_total);
        $sheet->getStyle('D'.$row)->getNumberFormat()->setFormatCode('"R$ "#,##0.00');
        $sheet->setCellValue('E'.$row, date('d/m/Y', strtotime($data['data_venda'])));
        
        $row++;
    }
} else {
    $sheet->setCellValue('A'.$row, 'Nenhuma venda registrada');
    $sheet->mergeCells('A'.$row.':E'.$row);
}

// Ajustar largura das colunas
foreach(range('A','E') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Rodapé
$sheet->setCellValue('A'.($row+2), 'Relatório gerado automaticamente pelo Sistema de Gestão de Estoque');
$sheet->mergeCells('A'.($row+2).':E'.($row+2));
$sheet->getStyle('A'.($row+2))->getFont()->setItalic(true);

// Gerar o arquivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

$conn->close();
exit;

