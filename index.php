<?php
include("conexao.php");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estoque de Alimentos</title>
    <div style="margin: 20px 0;">
    <a href="exportar_pdf.php" class="btn" style="margin-right: 10px; background-color:#e74c3c; color:white; padding:10px 15px; border-radius:5px; text-decoration:none;">
        <i class="fas fa-file-pdf"></i> Exportar PDF
    </a>
    <a href="exportar_excel.php" class="btn" style="background-color:#2ecc71; color:white; padding:10px 15px; border-radius:5px; text-decoration:none;">
        <i class="fas fa-file-excel"></i> Exportar Excel
    </a>
</div>


    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }

        h2 {
            color: #2c3e50;
            border-left: 6px solid #1abc9c;
            padding-left: 10px;
            margin-top: 40px;
        }

        form {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            animation: fadeIn 1s ease;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="submit"] {
            margin-top: 15px;
            padding: 12px 25px;
            font-size: 18px;
            background-color: #1abc9c;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #16a085;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            animation: fadeInUp 1s ease;
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        th {
            background-color: #1abc9c;
            color: white;
            font-size: 16px;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .fundo {
            color: #F5FFFA;
        }

        @media (max-width: 768px) {
            input[type="text"],
            input[type="number"],
            input[type="date"],
            select {
                font-size: 14px;
            }

            input[type="submit"] {
                font-size: 16px;
            }

            table, th, td {
                font-size: 14px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<h2><i class="fas fa-plus-circle"></i> Adicionar Alimento</h2>
<form action="adicionar_alimento.php" method="post">
    <label for="nome">Nome do Alimento:</label>
    <input type="text" id="nome" name="nome" required><br>

    <label for="preco">Preço:</label>
    <input type="number" id="preco" name="preco" step="0.01" required><br>

    <label for="quantidade_disponivel">Quantidade Disponível:</label>
    <input type="number" id="quantidade_disponivel" name="quantidade_disponivel" required><br>

    <input type="submit" value="Adicionar Alimento">
</form>

<h2><i class="fas fa-cash-register"></i> Registrar Venda</h2>
<form action="registrar_venda.php" method="post">
    <label for="alimento_id">Alimento:</label>
    <select id="alimento_id" name="alimento_id" required>
        <?php
        $sql_alimentos = "SELECT * FROM alimentos";
        $result_alimentos = $conn->query($sql_alimentos);

        if ($result_alimentos->num_rows > 0) {
            while($row = $result_alimentos->fetch_assoc()) {
                echo "<option value='{$row['id']}'>{$row['nome']}</option>";
            }
        } else {
            echo "<option value=''>Nenhum alimento disponível</option>";
        }
        ?>
    </select><br>

    <label for="quantidade_vendida">Quantidade Vendida:</label>
    <input type="number" id="quantidade_vendida" name="quantidade_vendida" required><br>

    <label for="data_venda">Data da Venda:</label>
    <input type="date" id="data_venda" name="data_venda" required><br>

    <input type="submit" value="Registrar Venda">
</form>

<h2><i class="fas fa-boxes"></i> Estoque Atual</h2>
<table>
    <thead>
        <tr>
            <th>Alimento</th>
            <th>Preço/unidade</th>
            <th>Quantidade Disponível</th>
            <th>Data da Última Atualização</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql_estoque = "SELECT a.nome, a.preco, e.quantidade_restante, e.data_atualizacao 
                        FROM estoques e 
                        JOIN alimentos a ON e.alimento_id = a.id 
                        ORDER BY e.data_atualizacao DESC";
        $result_estoque = $conn->query($sql_estoque);

        if ($result_estoque->num_rows > 0) {
            while($row = $result_estoque->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['nome']}</td>";
                echo "<td>R$ {$row['preco']}</td>";
                echo "<td>{$row['quantidade_restante']}</td>";
                echo "<td>{$row['data_atualizacao']}</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Nenhum dado de estoque disponível</td></tr>";
        }
        ?>
    </tbody>
</table>

<h2><i class="fas fa-chart-line"></i> Alimentos Vendidos</h2>
<table>
    <thead>
        <tr>
            <th>Nome do Alimento</th>
            <th>Quantidade Vendida</th>
            <th>Data da Venda</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $sql_vendas = "SELECT v.quantidade_vendida, v.data_venda, a.nome 
                       FROM vendas v 
                       JOIN alimentos a ON v.alimento_id = a.id 
                       ORDER BY v.data_venda DESC";
        $result_vendas = $conn->query($sql_vendas);

        if ($result_vendas->num_rows > 0) {
            while($row = $result_vendas->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['nome']}</td>";
                echo "<td>{$row['quantidade_vendida']}</td>";
                echo "<td>{$row['data_venda']}</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='3'>Nenhuma venda registrada</td></tr>";
        }
        ?>
    </tbody>
</table>

</body>
</html>

<?php $conn->close(); ?>