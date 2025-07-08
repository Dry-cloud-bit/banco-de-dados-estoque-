
<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $quantidade_disponivel = $_POST['quantidade_disponivel'];

    $sql = "INSERT INTO alimentos (nome, preco, quantidade_disponivel) 
            VALUES ('$nome', $preco, $quantidade_disponivel)";

    if ($conn->query($sql) === TRUE) {
        
        $alimento_id = $conn->insert_id;
        $data_atualizacao = date('Y-m-d');
        $sql_estoque = "INSERT INTO estoques (data_atualizacao, alimento_id, quantidade_restante) 
                        VALUES ('$data_atualizacao', $alimento_id, $quantidade_disponivel)";
        $conn->query($sql_estoque);

        echo "Alimento adicionado com sucesso!";
    } else {
        echo "Erro: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
    header("Location: index.php");
    exit();
}
?>