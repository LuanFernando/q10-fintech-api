<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    /**
     * @param Request $resquest
     * Retorna todas as contas cadastradas na base
     * 
    */
    public function getAllAccount(Request $request){
        $list = Account::get();

        return json_encode(array(
            'success' => true,
            'data'    => $list,
            'message' => (count($list) == 0 ? 'nenhuma conta foi encontrada' : 'lista de contas encontradas')
        ));
    }

    /**
     * @param Request $request
     * Cadastra uma nova conta na base de dados.
     * */ 
    public function newAccount(Request $request){

        try {
            
            //
            $validated = $request->validate([
                "agencia" => 'required|string|max:3',
                "conta"   => 'required|string|max:5',
                "digito"  => 'required|string|max:2',
                 "saldo"   => [
                    'required',
                    'numeric',
                    'regex:/^\d{1,13}(\.\d{1,2})?$/', // até 15 dígitos no total, 2 decimais
                ],
            ]);

            $account = new Account();
            $account->agencia = $validated['agencia'];
            $account->conta   = $validated['conta'];
            $account->digito  = $validated['digito'];
            $account->saldo   = $validated['saldo'];
            $account->status  = 'ativa';
            $account->save();

            return json_encode(array('success' => true, 'data' => $account, 'message' => 'conta cadastrada com sucesso'));

        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'data' => [], 'message' => $e->getMessage()));
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * Inativa uma conta na base de dados
     * */ 
    public function inactiveAccount(Request $request, int $id){

        if(!isset($id) || empty($id))
            return json_encode(array('success' => false, 'message' => 'não foi identificado o id da conta.'));

        try {
            $account = Account::findOrFail($id);
            
            if(!isset($account) || empty($account))
                return json_encode(array('success' => false, 'message' => 'não foi possivel encontra a conta.'));

            $account->status = 'bloqueada';
            $account->updated_at = date('Y-m-d H:m:s');
            $account->update();
            return json_encode(array('success' => true, 'message' => 'conta inativa com sucesso'));
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'message' => 'não foi possivel inativar a conta motivo: '.$e->getMessage()));
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * Atualiza os dados da conta do usuário
     * */ 
    public function updateAccount(Request $request, int $id){
        
        if(!isset($id) || empty($id))
            return json_encode(array('success' => false, 'message' => 'não foi possivel identificar o id da conta.'));

        try {

            $validated = $request->validate([
                "agencia" => 'required|string|max:3',
                "conta"   => 'required|string|max:5',
                "digito"  => 'required|string|max:2',
                "saldo"   => [
                    'required',
                    'numeric',
                    'regex:/^\d{1,13}(\.\d{1,2})?$/', // até 15 dígitos no total, 2 decimais
                ],
            ]);

            $account =  Account::findOrFail($id);

            if(!isset($account) || empty($account))
                return json_encode(array('success' => false, 'message' => 'não foi possivel encontra a conta.'));

            $account->agencia    = $validated['agencia'];
            $account->conta      = $validated['conta'];
            $account->digito     = $validated['digito'];
            $account->saldo      = $validated['saldo'];
            $account->updated_at = date('Y-m-d H:m:s');
            $account->update();

            return json_encode(array('success' => true, 'message' => 'conta atualizada com sucesso.'));

        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'message' => 'não foi possivel atualizar a conta motivo: '.$e->getMessage()));
        }

    }
}
