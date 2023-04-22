<html>
<head>
<title>Produtos</title>
</head>
<body>

<h1>Produtos</h1>

<ul>
    <?php   foreach($produtos as $produto):   ?>
    <li> <?php echo $produto->nome; ?>(<?php echo $produto->descricao; ?>) </li>
<?php endforeach; ?>
</ul>

</body>
<html>