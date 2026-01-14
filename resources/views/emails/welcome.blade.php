<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
        .wallet-id { background: #e5e7eb; padding: 15px; border-radius: 5px; font-family: monospace; margin: 20px 0; }
        .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Bem-vindo à Wallet API!</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $userName }}</strong>!</p>
            
            <p>Sua conta foi criada com sucesso. Agora você pode:</p>
            <ul>
                <li>✅ Consultar seu saldo</li>
                <li>✅ Fazer depósitos e saques</li>
                <li>✅ Transferir dinheiro para outros usuários</li>
                <li>✅ Ver seu histórico de transações</li>
            </ul>

            <p>Seu ID de carteira:</p>
            <div class="wallet-id">{{ $walletId }}</div>

            <p>Comece fazendo seu primeiro depósito e explore todas as funcionalidades!</p>
        </div>
        <div class="footer">
            <p>Wallet API - Sua carteira digital segura</p>
        </div>
    </div>
</body>
</html>
