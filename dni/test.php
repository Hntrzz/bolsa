<?php
require "./dni_val.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cifVal = validateSpanishID($_POST['test']);
    if ($cifVal['valid'] == false) {
        $errors[] = "CIF no válido";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="POST">
        <input type="text" id='test' name='test'>
        <input type="submit" value="enviar">
        <?php if (!empty($errors)): ?>
            <div style="color: red;">
                <?php foreach ($errors as $error): ?>
                    <?php echo $error; ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </form>
</body>

</html>