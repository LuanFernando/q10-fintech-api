<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    //
    public function getAllTransaction() {
        $list = Transaction::get();
        return json_encode(array('success' => true, 'data' => $list, 'message' => (count($list) == 0 ? 'nenhuma transação localizada' : 'lista transações')));
    }

    /**
     * @param Request $request
     * */ 
    public function newTransaction(Request $request) {

        try {
            $validated = $request->validate([
                'valor'          => [
                    'required',
                    'numeric',
                    'regex:/^\d{1,13}(\.\d{1,2})?$/', // até 15 dígitos no total, 2 decimais
                ],
                'data_transacao' => 'required|date_format:Y-m-d',
                'hora_transacao' => 'required|date_format:H:i:s',
                'status'         => 'required|string|max:20',
                'account_id'     => 'required|integer|exists:accounts,id',
                'contact_id'     => 'required|integer|exists:contacts,id'
            ]);

            $transaction = new Transaction();
            $transaction->fill($validated);
            $transaction->save();
            
            return json_encode(array('success' => true, 'data' => $transaction, 'message' => 'trasançãom efetuada com sucesso.'));

        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'message' => 'não foi possivel fazer uma nova transação motivo: '. $e->getMessage()));
        }
    }


}
