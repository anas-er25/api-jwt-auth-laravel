## Setting up JWT in Laravel

### Step 01: Create the project
Start by creating your Laravel project.
```bash
composer create-project laravel/laravel=10 project-name
```

### Step 02: Database Configuration
Configure your database in the `.env` file.

### Step 03: Create the API Controller
Create your API Controller to handle endpoints.

### Step 04: JWT Installation

#### Step #1
Install JWT by running the following command:

```bash
composer require tymon/jwt-auth
```
#### Step #2
Open the `app.php` file from the `/config` folder and make the following changes:

```php
'providers' => [
    // Other providers...
    Tymon\JWTAuth\Providers\LaravelServiceProvider::class,
],

'aliases' => [
    // Other aliases...
    'JWTAuth' => Tymon\JWTAuth\Facades\JWTAuth::class,
    'JWTFactory' => Tymon\JWTAuth\Facades\JWTFactory::class,
],
```

#### Step #3
Publish the JWT configuration file by running the command:

```bash
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
```

#### Step #4
Run migrations to create necessary tables:

```bash
php artisan migrate
```

#### Step #5
Generate the JWT secret key by running the command:

```bash
php artisan jwt:secret
```

#### Step #6
Open the `auth.php` file in the `/config` folder and add the following lines in the "guards" section:
```php
'guards' => [
    // Other guards...
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```


#### Step #7
Update the User model (`User.php`) as follows:
```php
<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
```

#### Step #8: Create API Controller
Run the following command to generate the API Controller:

```bash
php artisan make:controller Api/ApiController
```

Then, add the following code to your `ApiController`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{
    // User Register (POST, formdata)
    public function register(Request $request)
    {
        // Data validation
        $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed"
        ]);

        // User Model
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => Hash::make($request->password)
        ]);

        // Response
        return response()->json([
            "status" => true,
            "message" => "User registered successfully"
        ]);
    }

    // User Login (POST, formdata)
    public function login(Request $request)
    {
        // Data validation
        $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        // JWTAuth
        $token = JWTAuth::attempt([
            "email" => $request->email,
            "password" => $request->password
        ]);

        if (!empty($token)) {
            return response()->json([
                "status" => true,
                "message" => "User logged in successfully",
                "token" => $token
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "Invalid details"
        ]);
    }

    // User Profile (GET)
    public function profile()
    {
        $userdata = auth()->user();

        return response()->json([
            "status" => true,
            "message" => "Profile data",
            "data" => $userdata
        ]);
    }

    // To generate refresh token value
    public function refreshToken()
    {
        $newToken = auth()->refresh();

        return response()->json([
            "status" => true,
            "message" => "New access token",
            "token" => $newToken
        ]);
    }

    // User Logout (GET)
    public function logout()
    {
        auth()->logout();

        return response()->json([
            "status" => true,
            "message" => "User logged out successfully"
        ]);
    }
}
```

#### Step #9: Update Routes
Open the `api.php` file from the `/routes` folder and add the following routes:

```php
use App\Http\Controllers\Api\ApiController;

Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);

Route::group([
    "middleware" => ["auth:api"]
], function () {
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);
});

```

### These steps complete the setup for using JWT authentication in your Laravel project.
