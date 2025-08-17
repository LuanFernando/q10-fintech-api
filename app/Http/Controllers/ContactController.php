<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;

class ContactController extends Controller
{
    //
    public function getAllContact() {
        $list = Contact::get();
        return json_encode(
            array(
                'success' => true, 
                'data' => $list, 
                'message' => (count($list) == 0 ? 'nenhum contato encontrado' : 'lista de contato')));
    }

    /**
     * @param Request $request
     * */ 
    public function newContact(Request $request) {


        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:120',
                'pix'  => 'required|string|max:150' 
            ]);
    
            $contact = new Contact();
            $contact->nome   = strtoupper($validated['nome']);
            $contact->pix    = $validated['pix'];
            $contact->status = "ativo";
            $contact->save();

            return json_encode(array('success' => true, 'data' => $contact, 'message' => 'novo contato cadastrado com sucesso'));
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'message' => 'não foi possivel cadastrar novo contato motivo: '.$e->getMessage()));
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * */ 
    public function updateContact(Request $request, int $id) {
        if(!isset($id) && empty($id))
            return json_encode(array('success' => false, 'message' => 'não foi possivel identificar o id do contato'));


        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:120',
                'pix'  => 'required|string|max:150'
            ]);
    
            $contact = Contact::findOrFail($id);
            $contact->nome = $validated['nome'];
            $contact->pix  = $validated['pix'];
            $contact->update();
            return json_encode(array('success' => true, 'message' => 'dados de contato atualizado com sucesso'));

        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'message' => 'não foi possivel atualizar o contato motivo: '.$e->getMessage()));
        }
    }

    /**
     * @param Request $request
     * @param int $id
     * */ 
    public function inactiveContact(Request $request, int $id) {
        if(!isset($id) && empty($id))
            return json_encode(array('success' => false, 'message' => 'não foi possivel identificar o id do contato.'));

        try {
            $contact = Contact::findOrFail($id);
            $contact->status = 'inativo';
            $contact->update();
            return json_encode(array('success' => true, 'message' => 'contato inativado com sucesso'));
        } catch (\Exception $e) {
            return json_encode(array('success' => false, 'message' => 'não foi possivel inativar o contato motivo: '.$e->getMessage()));
        }

    }

}
