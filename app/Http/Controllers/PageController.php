<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function plans()
    {
        $plans = \App\Models\Plan::with(['featureValues.feature' => function($q) {
            $q->ordered();
        }])
        ->active()
        ->ordered()
        ->get();

        return view('pages.plans', compact('plans'));
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function sendContact(Request $request)
    {
        $contact = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        Log::info('Nova mensagem recebida pelo formulário de contato.', $contact);
        Mail::to(Setting::get('contact_email', 'contato@conectadoemsergipe.com.br'))
            ->send(new ContactMessageMail($contact));

        return back()->with('success', 'Sua mensagem foi recebida com sucesso!');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }
}
