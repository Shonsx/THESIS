<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Address;

class AccountController extends Controller
{
    public function showAccount()
    {
        return view('etry.account', ['user' => Auth::user()]);
    }

    public function showEditAccount()
    {
        return view('etry.editAccount', ['user' => Auth::user()]);
    }

    // Handle account update request
    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        $validatedData = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string',
            'tel' => 'required|string',
            'password' => 'nullable|string|min:7|confirmed',
        ]);
        // Allow all users (including admin) to update their name
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->tel = $validatedData['tel'];

        // Update password only if provided
        if (!empty($validatedData['password'])) {
            $user->password = $validatedData['password']; // No need for Hash::make()
        }

        $user->save();

        return redirect()->route('account')->with('success', 'Account updated successfully!');
    }

    public function updateUserRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->input('role');
        $user->save();
    
        return redirect()->back()->with('success', 'User role updated successfully.');
    }

    // AccountController.php

    public function showAddressSettings()
    {
        $user = Auth::user(); // Get the authenticated user

        // Check if user is logged in
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please log in first.');
        }

        // Pass the user data to the view
        return view('etry.settingsAccounts', compact('user'));
    }



    // Handle the form submission for updating the address
    public function updateAddress(Request $request)
    {
        $user = Auth::user(); 

        // Validate the input data
        $validatedData = $request->validate([
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'zip' => 'required|string|max:20',
        ]);

        // If the user has an existing address, update it. Otherwise, create a new one.
        $user->address()->updateOrCreate(
            [], // Empty conditions to update the first found address
            [
                'address' => $validatedData['address'],
                'city' => $validatedData['city'],
                'state' => $validatedData['state'],
                'zip' => $validatedData['zip'],
            ]
        );

        // Redirect back with success message
        return redirect()->route('account.settings.address')->with('success', 'Address updated successfully.');
    }


    // Handle account deletion and logout
    public function deleteAccount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->delete();
        Auth::logout();
        return redirect()->route('welcome')->with('success', 'Account deleted successfully.');
    }
}
