<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::latest()->get();

        return view('admin.client.client', compact('clients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        unset($data['document']);

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('clients/documents', 'public');
        }

        Client::create($data);

        return redirect()->route('admin.clients.index')->with('success', 'Client berhasil ditambahkan.');
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $this->validatedData($request);
        unset($data['document']);

        if ($request->hasFile('document')) {
            if ($client->document_path) {
                Storage::disk('public')->delete($client->document_path);
            }

            $data['document_path'] = $request->file('document')->store('clients/documents', 'public');
        }

        $client->update($data);

        return redirect()->route('admin.clients.index')->with('success', 'Client berhasil diperbarui.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->document_path) {
            Storage::disk('public')->delete($client->document_path);
        }

        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Client berhasil dihapus.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:1000',
            'project_name' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Prospective,Inactive',
            'notes' => 'nullable|string|max:2000',
            'document' => 'nullable|file|mimes:pdf|max:5120',
        ]);
    }
}
