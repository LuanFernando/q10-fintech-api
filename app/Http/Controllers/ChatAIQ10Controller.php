<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Contact;
use App\Models\Transaction;

class ChatAIQ10Controller extends Controller
{
    /**
     * @param Request $request
     * NOTE: Responsavel por classificar a intenção do usuário e chamar a função correta.
    */
    public function chat(Request $request) {

        $input = $output = $geminiKey = $messageFormatada = '';

        $validated = $request->validate([
            'input'      => 'required|string|max:120',
            'gemini_key' => 'required|string|max:240'
        ]);
        
        $input     = $validated['input'];
        $geminiKey = $validated['gemini_key'];

        // Prompt para classificar a intenção do input recebido
        $prompt   = $this->construirPrompt($input, true);

        $intencao = $this->consomeGeminiAI($prompt, $geminiKey);

        $intencao = str_replace('\n', '', (string) trim($intencao['candidates'][0]['content']['parts'][0]['text']));

        switch ($intencao) {
            case 'getAllAccount':
                $output = $this->getAllAccount();
                break;
            case 'getAllContact':
                $output = $this->getAllContact();
                break;
            case 'getAllTransaction':
                $output = $this->getAllTransaction();
                break;
            default:
                $output = 'não foi possivel identificar sua intenção.';
                break;
        }

        // Traduzi os dados do cliente para algo claro e direto
        if($output != 'não foi possivel identificar sua intenção.') {
            $prompt = $this->construirPrompt($output, false);
            $messageFormatada = $this->consomeGeminiAI($prompt, $geminiKey);
            $messageFormatada = str_replace('\n', '', (string) trim($messageFormatada['candidates'][0]['content']['parts'][0]['text']));
        }

        return json_encode(array('success' => true, 'data' => $output, 'message' => $messageFormatada, 'intencao' => $intencao));
    }

    /**
     * @param String $input
     * @param bool $intencao
     * @return String $prompt
    */
    public function construirPrompt(String $input, $intencao = false) : String {

        $prompt = "";

        if($intencao == true) {
            $prompt = "
                    Assuma o papel de um assistente especializado em classificação de intenções.
                    Sua única e exclusiva responsabilidade é analisar a mensagem recebida e retornar apenas a intenção correspondente, exatamente como está definida na lista abaixo.
    
                    Regras obrigatórias:
    
                    Nunca invente novas intenções.
    
                    Retorne somente o nome da intenção, sem explicações extras.
    
                    Caso a mensagem não corresponda a nenhuma intenção da lista, retorne *unknown*.
    
                    Exemplo:
                    Mensagem: Retorne todas as transações realizadas
                    Resposta: getAllTransaction
    
                    Lista de intenções disponíveis:
    
                    [
                        getAllAccount,
                        getAllContact,
                        getAllTransaction
                    ]
    
                    Mensagem recebida: [$input]
            ";
        } else {
            $prompt = "
                Assuma o papel de um gestor financeiro da Q10 Fintech.
                Sua responsabilidade é analisar os dados financeiros do cliente e traduzir essas informações para algo
                simples e direto, de modo que o cliente compreenda facilmente sua situação financeira.

                Entrada:

                Dados financeiros do cliente: [$input]

                Saída esperada:

                Um resumo claro e objetivo da situação financeira.

                Linguagem acessível, sem termos técnicos complexos.

                Se os dados forem insuficientes ou inconsistentes, retorne *Informações financeiras insuficientes para análise.*
            ";
        }

        return $prompt;
    } 

    /**
     * @param String $prompt
     * @param String $geminiKey
     * @return String $intencao
     * */
    public function consomeGeminiAI($prompt, $geminiKey) {

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key={$geminiKey}";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => "$prompt"]
                    ]
                ]
            ]
        ];

        // Inicializa o cURL
        $ch = curl_init($url);

        // Configurações do cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Ignora a verificação SSL - segundo a polita da nominatim precisa passar estes cabeçalhos
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        // Executa a solicitação
        $response = curl_exec($ch);

        // Verifica erros no cURL
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        curl_close($ch);

        // Converte a resposta para JSON
        $data = json_decode($response, true);
        
        return $data;
    } 

    /**
     * NOTE: Retorna todos os registros
     * @return array|null $list
     * */ 
    public function getAllAccount() {
        $list = Account::get();
        return $list;
    }

    /**
     * NOTE: Retorna todos os registros
     * @return array|null $list
     * */ 
    public function getAllContact() {
        $list = Contact::get();
        return $list;
    }

    /**
     * NOTE: Retorna todos os registros
     * @return array|null $list
    * */  
    public function getAllTransaction() {
        $list = getAllTransaction::get();
        return $list;
    }

}
