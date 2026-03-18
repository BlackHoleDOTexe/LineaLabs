<form method="POST">

Nome:
<input type="text" name="nome">

<button type="submit">Enviar</button>

</form>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = $_POST["nome"];

    echo "Olá " . $nome;

}

?>