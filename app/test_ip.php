<?php
// Script para ajudar a identificar o IP e testar conectividade
$ip = gethostbyname(gethostname());
?>
<!DOCTYPE html>
<html>
<head>
    <title>Teste de Conexão</title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 50px; background: #f4f4f9; }
        .box { background: white; padding: 20px; border-radius: 10px; display: inline-block; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h1 { color: #22c55e; }
        code { background: #eee; padding: 5px 10px; border-radius: 5px; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Servidor Online!</h1>
        <p>Tente acessar este endereço no seu celular:</p>
        <code>http://<?php echo $ip; ?>/fab/app/mobile.html</code>
        <p style="margin-top:20px; color: #666;">Se ainda der timeout, verifique o Firewall do Windows.</p>
    </div>
</body>
</html>
