<?php
include "conexao.php";

// Atualizar recado
if(isset($_POST['atualiza'])){
    $idatualiza = intval($_POST['id']);
    $nome       = mysqli_real_escape_string($conexao, $_POST['nome']);
    $email      = mysqli_real_escape_string($conexao, $_POST['email']);
    $msg        = mysqli_real_escape_string($conexao, $_POST['msg']);

    $sql = "UPDATE recados SET nome='$nome', email='$email', mensagem='$msg' WHERE id=$idatualiza";
    mysqli_query($conexao, $sql) or die("Erro ao atualizar: " . mysqli_error($conexao));
    header("Location: moderar.php");
    exit;
}

// Excluir recado
if(isset($_GET['acao']) && $_GET['acao'] == 'excluir'){
    $id = intval($_GET['id']);
    mysqli_query($conexao, "DELETE FROM recados WHERE id=$id") or die("Erro ao deletar: " . mysqli_error($conexao));
    header("Location: moderar.php");
    exit;
}

// Editar recado
$editar_id = isset($_GET['acao']) && $_GET['acao'] == 'editar' ? intval($_GET['id']) : 0;
$recado_editar = null;
if($editar_id){
    $res = mysqli_query($conexao, "SELECT * FROM recados WHERE id=$editar_id");
    $recado_editar = mysqli_fetch_assoc($res);
}
?>
<?php
include "conexao.php"; // conexão com MySQL + variáveis do Cloudinary

// Função para deletar imagem do Cloudinary
function deletarImagemCloudinary($public_id, $cloud_name, $api_key, $api_secret) {
    $timestamp = time();
    $string_to_sign = "public_id=$public_id&timestamp=$timestamp$api_secret";
    $signature = sha1($string_to_sign);

    $data = [
        'public_id' => $public_id,
        'timestamp' => $timestamp,
        'api_key' => $api_key,
        'signature' => $signature
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/destroy");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

/*
COMPARAÇÃO: No código de recados/pedidos
- Não há função de deletar arquivos
- Não existe upload de imagem
- Apenas se deleta o registro do banco
*/

// Excluir produto
if(isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $res = mysqli_query($conexao, "SELECT imagem_url FROM produtos WHERE id = $id");
    $dados = mysqli_fetch_assoc($res);

    if($dados && !empty($dados['imagem_url'])) {
        $url = $dados['imagem_url'];
        $parts = explode("/", $url);
        $filename = end($parts);
        $public_id = pathinfo($filename, PATHINFO_FILENAME);
        deletarImagemCloudinary($public_id, $cloud_name, $api_key, $api_secret);
    }

    mysqli_query($conexao, "DELETE FROM produtos WHERE id = $id") or die("Erro ao excluir: " . mysqli_error($conexao));
    header("Location: moderar.php"); //substituir se estiver diferente
    exit;
}

/*
COMPARAÇÃO:
- Código de recados/pedidos: deletar não manipula imagens, só remove o registro
- Aqui é necessário deletar a imagem no Cloudinary antes de excluir do banco
*/

// Editar produto
if(isset($_POST['editar'])) {
    $id = intval($_POST['id']);
    $nome = mysqli_real_escape_string($conexao, $_POST['nome']);
    $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $preco = floatval($_POST['preco']);

    $update_sql = "UPDATE produtos SET nome='$nome', descricao='$descricao', preco=$preco WHERE id=$id";
    mysqli_query($conexao, $update_sql) or die("Erro ao atualizar: " . mysqli_error($conexao));
    header("Location: moderar.php");
    exit;
}

/*
COMPARAÇÃO:
- Código de recados/pedidos: não há edição inline
- Aqui o sistema permite editar nome, descrição e preço, mas não imagem
*/


// Selecionar produtos para exibição
$editar_id = isset($_GET['editar']) ? intval($_GET['editar']) : 0;
$produtos = mysqli_query($conexao, "SELECT * FROM produtos ORDER BY id DESC");

/*
COMPARAÇÃO:
- Código de recados/pedidos: SELECT * FROM recados ORDER BY id DESC
- Aqui seleciona produtos com imagens e preço
*/
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8"/>
<title>Moderar pedidos</title>
<link rel="stylesheet" href="style.css"/>
</head>
<body>
<div id="main">
<div id="geral">
<div id="header">
    <h1>Mural de pedidos</h1>
</div>

<?php if($recado_editar): ?>
<div id="formulario_mural">
<form method="post">
    <label>Nome:</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($recado_editar['nome']); ?>"/><br/>
    <label>Email:</label>
    <input type="text" name="email" value="<?php echo htmlspecialchars($recado_editar['email']); ?>"/><br/>
    <label>Mensagem:</label>
    <textarea name="msg"><?php echo htmlspecialchars($recado_editar['mensagem']); ?></textarea><br/>
    <input type="hidden" name="id" value="<?php echo $recado_editar['id']; ?>"/>
    <input type="submit" name="atualiza" value="Modificar Recado" class="btn"/>
</form>
</div>
<?php endif; ?>

<?php
$seleciona = mysqli_query($conexao, "SELECT * FROM recados ORDER BY id DESC");
if(mysqli_num_rows($seleciona) <= 0){
    echo "<p>Nenhum pedido no mural!</p>";
}else{
    while($res = mysqli_fetch_assoc($seleciona)){
        echo '<ul class="recados">';
        echo '<li><strong>ID:</strong> ' . $res['id'] . ' | 
              <a href="moderar.php?acao=excluir&id=' . $res['id'] . '">Remover</a> | 
              <a href="moderar.php?acao=editar&id=' . $res['id'] . '">Modificar</a></li>';
        echo '<li><strong>Nome:</strong> ' . htmlspecialchars($res['nome']) . '</li>';
        echo '<li><strong>Email:</strong> ' . htmlspecialchars($res['email']) . '</li>';
        echo '<li><strong>Mensagem:</strong> ' . nl2br(htmlspecialchars($res['mensagem'])) . '</li>';
        echo '</ul>';
    }
}
?>

<div id="footer">
</div>
</div>
</div>
</body>
</html>
