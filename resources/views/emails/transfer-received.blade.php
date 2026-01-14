<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferência Recebida</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
        .amount { font-size: 32px; color: #10b981; font-weight: bold; text-align: center; margin: 20px 0; }
        .details { background: white; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #10b981; }
        .details-item { display: flex; justify-content: space-between; margin: 10px 0; }
        .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>💰 Transferência Recebida!</h1>
        </div>
        <div class="content">
            <p>Olá, <strong>{{ $userName }}</strong>!</p>
            
            <p>Você recebeu uma transferência:</p>

            <div class="amount">R$ {{ $amount }}</div>

            <div class="details">
                <div class="details-item">
                    <span><strong>De:</strong></span>
                    <span>{{ $senderEmail }}</span>
                </div>
                <div class="details-item">
                    <span><strong>Novo Saldo:</strong></span>
                    <span>R$ {{ $newBalance }}</span>
                </div>
            </div>

            <p style="text-align: center; color: #6b7280;">
                O dinheiro já está disponível na sua carteira!
            </p>
        </div>
        <div class="footer">
            <p>Wallet API - Notificações em tempo real</p>
        </div>
    </div>
</body>
</html>
