<?php
include_once('config.php');

$id = $nome = $email = $genero = $data_nasc = "";
$editando = false;
$successMsg = "";

// Se for edição, busca os dados
if (isset($_GET['id'])) {
    $editando = true;
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM usuarios WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $nome = $row['nome'];
        $email = $row['email'];
        $genero = $row['genero'];
        $data_nasc = $row['data_nasc'];
    }
    $stmt->close();
}

// Salvar alterações
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $genero = trim($_POST['genero']);
    $data_nasc = trim($_POST['data_nasc']);

    if ($id) {
        // Atualizar
        $sql = "UPDATE usuarios SET nome=?, email=?, genero=?, data_nasc=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nome, $email, $genero, $data_nasc, $id);
        if ($stmt->execute()) {
            $successMsg = "Usuário atualizado com sucesso!";
        } else {
            $successMsg = "Erro ao atualizar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        // Inserir novo
        $sql = "INSERT INTO usuarios (nome, email, genero, data_nasc) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nome, $email, $genero, $data_nasc);
        if ($stmt->execute()) {
            $successMsg = "Usuário cadastrado com sucesso!";
        } else {
            $successMsg = "Erro ao cadastrar: " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $editando ? "Editar Usuário" : "Novo Usuário"; ?></title>
</head>
<body>
    <h1><?php echo $editando ? "Editar Usuário" : "Novo Usuário"; ?></h1>
    <form method="POST" action="">
        <?php if ($editando): ?>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
        <?php endif; ?>
        <p>
            <label>Nome:</label>
            <input type="text" name="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
        </p>
        <p>
            <label>E-mail:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </p>
        <p>
            <label>Gênero:</label>
            <select name="genero" required>
                <option value="">Selecione</option>
                <option value="Masculino" <?php if($genero=="Masculino") echo "selected"; ?>>Masculino</option>
                <option value="Feminino" <?php if($genero=="Feminino") echo "selected"; ?>>Feminino</option>
                <option value="Outro" <?php if($genero=="Outro") echo "selected"; ?>>Outro</option>
            </select>
        </p>
        <p>
            <label>Data de Nascimento:</label>
            <input type="date" name="data_nasc" value="<?php echo htmlspecialchars($data_nasc); ?>" required>
        </p>
        <p>
            <button type="submit"><?php echo $editando ? "Salvar Alterações" : "Cadastrar"; ?></button>
            <a href="index.php">Voltar</a>
        </p>
    </form>
    <p style="color:green;"><?php echo $successMsg; ?></p>
</body>
</html>
