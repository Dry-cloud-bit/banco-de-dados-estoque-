<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $alimento_id = $_POST['alimento_id'];
    $quantidade_vendida = $_POST['quantidade_vendida'];
    $data_venda = $_POST['data_venda'];

    
    $sql_venda = "INSERT INTO vendas (alimento_id, quantidade_vendida, data_venda) 
                  VALUES ($alimento_id, $quantidade_vendida, '$data_venda')";

    if ($conn->query($sql_venda) === TRUE) {
        
        $sql_update_estoque = "UPDATE estoques 
                               SET quantidade_restante = quantidade_restante - $quantidade_vendida, 
                                   data_atualizacao = '$data_venda' 
                               WHERE alimento_id = $alimento_id";
        $conn->query($sql_update_estoque);

        echo "Venda registrada com sucesso!";
    } else {
        echo "Erro: " . $sql_venda . "<br>" . $conn->error;
    }

    $conn->close();
    header("Location: index.php");
    exit();
}
?>