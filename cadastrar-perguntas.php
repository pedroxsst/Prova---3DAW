<!DOCTYPE html>
<html>
<head>
<title>Admin</title>
</head>
<body>

<h1>Cadastrar Pergunta Discursiva</h1>

<form action="" method="POST">
    <p>
        <label>Pergunta:</label><br>
        <textarea name="pergunta" rows="4" cols="50" required><?php echo isset($pergunta) ? htmlspecialchars($pergunta) : ''; ?></textarea>
    </p>
    <p>
        <button type="submit">Salvar</button>
    </p>
</form>

</body>
</html>
<?php
// Função para limpar e tratar os dados do formulário
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$pergunta = "";
$successMsg = "";

// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "seubanco"; // Altere para o nome do seu banco de dados

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["pergunta"])) {
        $successMsg = "Pergunta é obrigatória.";
    } else {
        $pergunta = test_input($_POST["pergunta"]);

        // Cria conexão
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Checa conexão
        if ($conn->connect_error) {
            die("Falha na conexão: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("INSERT INTO perguntas (pergunta) VALUES (?)");
        $stmt->bind_param("s", $pergunta);

        if ($stmt->execute()) {
            $successMsg = "Pergunta cadastrada com sucesso!";
            $pergunta = "";
        } else {
            $successMsg = "Erro ao cadastrar: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    }
}
?>
<p style="color:green;"> <?php echo $successMsg; ?> </p>
