<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Moderator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Введите Email.',
            'email.email' => 'Введите корректный Email.',
            'password.required' => 'Введите пароль.',
        ]);

        $masterEmail = config('admin.master_email');
        $masterPassword = config('admin.master_password');
        


        // Check Master Admin (Config-based validation)
        if ($request->email === $masterEmail && $request->password === $masterPassword) {
            // Find or create a DB record for Master Admin to attach token to
            // We do NOT store the config password in DB, just use it as a placeholder identity
            $admin = Moderator::firstOrCreate(
                ['email' => $masterEmail],
                ['name' => 'Master Admin', 'password' => Hash::make(\Str::random(32))]
            );

            // Issue token with 'master' ability
            $token = $admin->createToken('admin-token', ['master'])->plainTextToken;

            return response()->json([
                'token' => $token,
                'role' => 'master',
                'user' => $admin
            ]);
        }

        // Check Regular Moderator (DB-based validation)
        $moderator = Moderator::where('email', $request->email)->first();

        if (! $moderator || ! Hash::check($request->password, $moderator->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный email или пароль.'],
            ]);
        }

        $token = $moderator->createToken('admin-token', ['moderator'])->plainTextToken;

        return response()->json([
            'token' => $token,
            'role' => 'moderator',
            'user' => $moderator
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return $request->user();
    }

    public function indexModerators(Request $request)
    {
        // Self-heal: Ensure Master Admin exists in the list
        $masterEmail = config('admin.master_email');
        if (!Moderator::where('email', $masterEmail)->exists()) {
             Moderator::create([
                'email' => $masterEmail,
                'name' => 'Master Admin',
                'password' => Hash::make(config('admin.master_password') ?? '123456') // Basic fallback
            ]);
        }

        $query = Moderator::query();

        // Search
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhereRaw("TO_CHAR(created_at AT TIME ZONE 'UTC' AT TIME ZONE 'Europe/Moscow', 'DD.MM.YYYY') ILIKE ?", ["%{$search}%"]);
            });
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        return $query->paginate($perPage);
    }

    public function storeModerator(Request $request)
    {
        if ($request->user()->email !== config('admin.master_email')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'email' => 'required|email|unique:moderators,email',
            'password' => [
                'required',
                'min:10',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ],
            'name' => 'required|string',
        ], [
            'email.required' => 'Введите Email.',
            'email.email' => 'Введите корректный Email.',
            'email.unique' => 'Такой Email уже зарегистрирован.',
            'password.required' => 'Введите пароль.',
            'password.min' => 'Пароль должен содержать минимум :min символов.',
            'password.regex' => 'Пароль должен содержать заглавные и строчные буквы, а также цифры.',
            'name.required' => 'Введите имя.',
        ]);
        
        $moderator = Moderator::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json($moderator, 201);
    }
    
    public function showModerator(Request $request, $id)
    {
        if ($request->user()->email !== config('admin.master_email')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        return Moderator::findOrFail($id);
    }

    public function updateModerator(Request $request, $id)
    {
        if ($request->user()->email !== config('admin.master_email')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $moderator = Moderator::findOrFail($id);
        
        $validateRules = [
            'email' => 'required|email|unique:moderators,email,' . $id,
            'name' => 'required|string',
        ];
        
        if ($request->filled('password')) {
            $validateRules['password'] = [
                'required',
                'min:10',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
            ];
        }

        $request->validate($validateRules, [
            'email.required' => 'Введите Email.',
            'email.email' => 'Введите корректный Email.',
            'email.unique' => 'Такой Email уже зарегистрирован.',
            'password.required' => 'Введите пароль.',
            'password.min' => 'Пароль должен содержать минимум :min символов.',
            'password.regex' => 'Пароль должен содержать заглавные и строчные буквы, а также цифры.',
            'name.required' => 'Введите имя.',
        ]);

        $moderator->name = $request->name;
        $moderator->email = $request->email;
        if ($request->filled('password')) {
            $moderator->password = Hash::make($request->password);
        }
        $moderator->save();

        return response()->json($moderator);
    }
    
    public function deleteModerator(Request $request, $id)
    {
         if ($request->user()->email !== config('admin.master_email')) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $moderator = Moderator::findOrFail($id);
        
        if ($moderator->email === config('admin.master_email')) {
            return response()->json(['message' => 'Cannot delete master admin'], 403);
        }
        
        $moderator->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function stats()
    {
        $usersCount = \App\Models\User::count();
        $bidsCount = \App\Models\Order::count();
        
        $chartData = [
            'labels' => [],
            'data' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::now()->subDays($i);
            
            // Simple whereDate logic matching OrderController (Client Panel)
            // This relies on the App timezone configuration which seems to work for the client side
            $count = \App\Models\Order::whereDate('created_at', $date->format('Y-m-d'))->count();
            
            $chartData['labels'][] = $date->locale('ru')->isoFormat('dd');
            $chartData['data'][] = $count;
        }

        return response()->json([
            'users_count' => $usersCount,
            'bids_count' => $bidsCount,
            'chart_data' => $chartData
        ]);
    }

    public function users(Request $request)
    {
        $query = \App\Models\User::query();

        // Search
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $cleanedSearch = preg_replace('/\D/', '', $search);

            $query->where(function($q) use ($search, $cleanedSearch) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('organization_name', 'ilike', "%{$search}%")
                  ->orWhere('inn', 'ilike', "%{$search}%")
                  ->orWhereRaw("TO_CHAR(created_at AT TIME ZONE 'UTC' AT TIME ZONE 'Europe/Moscow', 'DD.MM.YYYY') ILIKE ?", ["%{$search}%"]);
                
                if (!empty($cleanedSearch)) {
                    $q->orWhereRaw("REGEXP_REPLACE(phone, '\D', '', 'g') LIKE ?", ["%{$cleanedSearch}%"]);
                }
            });
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSorts = ['id', 'name', 'organization_name', 'phone', 'email', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        return $query->paginate($perPage);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'name' => 'required|string',
            'organization_name' => 'required|string',
            'inn' => 'required|string',
        ], [
             'email.email' => 'Введите корректный Email.',
             'email.unique' => 'Такой Email уже зарегистрирован.',
             'phone.required' => 'Введите телефон.',
             'phone.unique' => 'Такой телефон уже зарегистрирован.',
             'name.required' => 'Введите ФИО.',
             'organization_name.required' => 'Введите название организации.',
             'inn.required' => 'Введите ИНН.',
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'organization_name' => $request->organization_name,
            'inn' => $request->inn,
            'password' => null, // Password not used (SMS auth)
        ]);

        return response()->json($user, 201);
    }

    public function showUser($id)
    {
        return \App\Models\User::findOrFail($id);
    }

    public function updateUser(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        $validateRules = [
            'email' => 'nullable|email|unique:users,email,' . $id,
            'phone' => 'required|string|unique:users,phone,' . $id,
            'name' => 'required|string',
            'organization_name' => 'required|string',
            'inn' => 'required|string',
        ];
        
        $request->validate($validateRules, [
             'email.email' => 'Введите корректный Email.',
             'email.unique' => 'Такой Email уже зарегистрирован.',
             'phone.required' => 'Введите телефон.',
             'phone.unique' => 'Такой телефон уже зарегистрирован.',
             'name.required' => 'Введите ФИО.',
             'organization_name.required' => 'Введите название организации.',
             'inn.required' => 'Введите ИНН.',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->organization_name = $request->organization_name;
        $user->inn = $request->inn;

        
        $user->save();

        return response()->json($user);
    }

    public function deleteUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    public function bids(Request $request)
    {
        $query = \App\Models\Order::query()->with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                // Search by Order ID
                $q->whereRaw("CAST(id AS TEXT) LIKE ?", ["%{$search}%"]);

                // Special handling for #ID search
                if (strpos($search, '#') === 0) {
                     $cleanId = substr($search, 1);
                     $q->orWhereRaw("CAST(id AS TEXT) LIKE ?", ["%{$cleanId}%"]);
                }

                // Search by User Name
                $q->orWhereHas('user', function($u) use ($search) {
                      $u->where('name', 'ilike', "%{$search}%")
                        ->orWhere('phone', 'ilike', "%{$search}%");
                  })
                  // Search by Date (DD.MM.YYYY)
                  ->orWhereRaw("TO_CHAR(created_at AT TIME ZONE 'UTC' AT TIME ZONE 'Europe/Moscow', 'DD.MM.YYYY') ILIKE ?", ["%{$search}%"]);
            });
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSorts = ['id', 'type', 'status', 'created_at'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
             $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        return $query->paginate($perPage);
    }

    public function deleteBid($id)
    {
         $bid = \App\Models\Order::findOrFail($id);
         $bid->delete();
         return response()->json(['message' => 'Bid deleted']);
    }
}
