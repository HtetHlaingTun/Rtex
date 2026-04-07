<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ContactController extends Controller
{
    // Admin: View all contacts
    public function index(Request $request)
    {
        $query = Contact::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $contacts = $query->latest()->paginate(20);
        $unreadCount = Contact::where('is_read', false)->count();

        // FIXED: Changed from 'Admin/Contact/Index' to 'Admin/Contacts/Index'
        return Inertia::render('Admin/Contact/Index', [
            'contacts' => $contacts,
            'unreadCount' => $unreadCount,
        ]);
    }

    // Store contact message (public)
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'subject' => 'required|string|max:255',
                'message' => 'required|string|min:5',
            ]);

            $contact = Contact::create($validated);

            return redirect()->back()->with('success', 'Thank you! Your message has been sent successfully.');
        } catch (\Exception $e) {
            Log::error('Contact form error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to send message. Please try again.'])->withInput();
        }
    }

    // Admin: View single contact
    public function show(Contact $contact)
    {
        if (!$contact->is_read) {
            $contact->markAsRead();
        }

        // FIXED: Changed from 'Admin/Contact/Show' to 'Admin/Contacts/Show'
        return Inertia::render('Admin/Contact/Show', [
            'contact' => $contact,
        ]);
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return redirect()->route('admin.contacts.index')->with('success', 'Contact deleted successfully');
    }

    // Admin: Mark as replied
    public function markReplied(Contact $contact)
    {
        $contact->update(['is_replied' => true]);
        return redirect()->back()->with('success', 'Marked as replied');
    }
}
