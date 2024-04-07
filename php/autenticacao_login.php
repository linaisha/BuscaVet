<?php

require_once 'vendor/autoload.php'; // Carregar a biblioteca do Twilio

use Twilio\Rest\Client;

// Configurações do Twilio (substitua com suas próprias credenciais)
$accountSid = 'AC696815bcb5e48d64643e0d311350001e';
$authToken = 'b31c603ba66a611f9d1dd31bf5ba6ce8';
$twilioPhoneNumber = '+17178077769';

// Função para enviar o código de verificação via SMS
function sendVerificationCode($phoneNumber, $code) {
    global $accountSid, $authToken, $twilioPhoneNumber;

    // Inicializar o cliente Twilio
    $twilio = new Client($accountSid, $authToken);

    try {
        // Enviar a mensagem SMS com o código de verificação
        $message = $twilio->messages
            ->create($phoneNumber, // Número de telefone do destinatário
                     array(
                         "from" => $twilioPhoneNumber,
                         "body" => "Seu código de verificação é: $code"
                     )
            );
        // Se a mensagem for enviada com sucesso
        return true;
    } catch (Exception $e) {
        // Se ocorrer algum erro no envio da mensagem
        return false;
    }
}

// Função para gerar um código de verificação aleatório
function generateVerificationCode($length = 6) {
    $chars = '0123456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $code;
}

// Exemplo de uso:
$phoneNumber = '+5541997306981'; // Número de telefone do destinatário

// Gerar um código de verificação aleatório
$verificationCode = generateVerificationCode();

// Enviar o código de verificação via SMS
if (sendVerificationCode($phoneNumber, $verificationCode)) {
    echo "O código de verificação foi enviado com sucesso para $phoneNumber.";
} else {
    echo "Ocorreu um erro ao enviar o código de verificação.";
}
?>