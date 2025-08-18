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
            case 'newTransaction':
                $output = $this->newTransaction((string) $validated['input'], $geminiKey);
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
     * @param bool $dadosTransacao
     * @return String $prompt
    */
    public function construirPrompt(String $input, $intencao = false, $dadosTransacao = false) : String {

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

                    Mensagem: Faça um PIX para o João Lima, no valor de R$ 125,98
                    Resposta: newTransaction

                    Mensagem: Envie R$ 2,09 para a Maria
                    Resposta: newTransaction
    
                    Lista de intenções disponíveis:
    
                    [
                        getAllAccount,
                        getAllContact,
                        getAllTransaction,
                        newTransaction
                    ]
    
                    Mensagem recebida: [$input]
            ";
        } else {

            if($dadosTransacao == true) {
                $prompt = '
                    Analise os dados financeiros recebidos.
                    Sua responsabilidade é verificar se todos os campos necessários para efetuar uma transação estão presentes.

                    Regras obrigatórias:

                    Se todos os campos forem encontrados, retorne apenas os dados extraídos em formato JSON válido, seguindo a estrutura abaixo.

                    Se algum campo estiver ausente ou inconsistente, retorne:

                    { "erro": "Dados insuficientes para efetuar a transação" }

                    Estrutura esperada do JSON de saída:

                    {
                    "valor": 100.99,
                    "destinatario": "João Lima"
                    }

                    Campos obrigatórios:

                    valor (número decimal)
                    destinatario (string, nome completo)

                    Exemplo de mensagem e saida esperada:

                    Mensagem: Envie um Pix para o João Lima, no valor de R$ 23,98
                    Saida: 
                        {
                            "valor": 100.99,
                            "destinatario": "João Lima"
                        }

                    Mensagem: Faça um Pix de 23,78 para o Maria Gomes
                    Saida: 
                        {
                            "valor": 23.78,
                            "destinatario": "Maria Gomes"
                        }

                    Dados para transação: ['.$input.']
                ';

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

    /**
     * @param string $input
     * @param string $geminiKey
     * @return array|null $newTransaction
     * */ 
    public function newTransaction(String $input, String $geminiKey) {

        $newTransaction = null;

        if(isset($input) && !empty($input)) {

            // Extrai os dados necessarios para fazer a transação.
            $prompt   = $this->construirPrompt($input, false, true);

            $dadosTransacao = $this->consomeGeminiAI($prompt, $geminiKey);

            // Captura apenas o texto retornado pelo modelo
            $raw = $dadosTransacao['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Remove blocos de markdown (```json ... ```)
            $clean = preg_replace('/```(json)?|```/', '', $raw);

            // Remove espaços em excesso e quebras de linha
            $clean = trim($clean);

            // Agora decodifica
            $dadosTransacao = json_decode($clean, true);

            // Seta Account 1 default
            $account_id = 1;

            // Resgata o contado baseando no nome recebido
            // Faz a transação e retorna a mensagem para o cliente.
            $valor        =  ($dadosTransacao['valor'] == 0 ? 0.00 : $dadosTransacao['valor']);
            $destinatario =  $dadosTransacao['destinatario'];

            if($valor > 0 && !empty($destinatario)) {
                $valores = [
                    'valor'          => $valor,
                    'data_transacao' => (string) date('Y-m-d'),
                    'hora_transacao' => (string) date('H:i:s'),
                    'status'         => 'sucesso',
                    'account_id'     => $account_id
                ];

                // Seta o novo saldo da conta
                $account        = Account::findOrFail($account_id);
                $account->saldo = str_replace(',', '.', (string) ($account->saldo - $valor)); 
                $account->update();

                // Contato
                $contact = Contact::where('nome', 'like', '%'.$destinatario.'%')->first();
                $valores['contact_id'] = $contact->id;

                // Processa a transação
                $transaction = new Transaction();
                $transaction->fill($valores);
                $transaction->save();
                
                // Retorna
                $newTransaction = $transaction; 
            }
        }

        return $newTransaction;
    }
}
