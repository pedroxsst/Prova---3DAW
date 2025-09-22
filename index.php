<?php
// Inclui o arquivo de conexão
include_once('config.php');

// Query para selecionar todos os usuários
$sql = "SELECT id, nome, email, data_nasc FROM usuarios ORDER BY nome ASC";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
        <head>
            <title>Lista de Usuários</title>
        </head>
    <body>

        <h1>Lista de Usuários</h1>
        <p><a href="novo-usuario.php">Adicionar Novo Usuário</a></p>

        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Data de Nascimento</th>
                <th>Gênero</th>
            </tr>

            <?php
        // Verifica se a consulta retornou resultados
            if ($resultado->num_rows > 0) {
        // Loop
            while($usuario = $resultado->fetch_assoc()) {
            echo "<tr>";                
            echo "<td>" . $usuario['id'] . "</td>";
            echo "<td>" . htmlspecialchars($usuario['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['email']) . "</td>";
            echo "<td>" . htmlspecialchars($usuario['genero']) . "</td>";
            echo "<td>" . $usuario['data_nasc'] . "</td>";
            echo "<td>";
            echo "<a href='editar.php?id=" . $usuario['id'] . "'>Editar</a> | ";
            echo "<a href='salvar-usuario.php?acao=excluir&id=" . $usuario['id'] . "' onclick='return confirm(\"Tem certeza que deseja excluir este usuário?\")'>Excluir</a>";
            echo "</td>";                echo "</tr>";
            }
            } else {
                echo "<tr><td colspan='5'>Nenhum usuário encontrado</td></tr>";
             }
         ?>
    </table>

    </body>
</html>

<?php
// Fecha a conexão com o banco
    $conn->close();
?>