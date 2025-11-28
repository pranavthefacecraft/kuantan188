<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        Log::info('Login form requested', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
        ]);
        
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        Log::info('Login attempt started', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => session()->getId(),
            'csrf_token' => $request->input('_token'),
            'all_input' => $request->except('password'),
        ]);

        $this->validateLogin($request);

        Log::info('Login validation passed');

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            Log::warning('Too many login attempts', ['email' => $request->input('email')]);
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            Log::info('Login successful', [
                'user_id' => Auth::id(),
                'email' => $request->input('email'),
            ]);
            
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            $this->clearLoginAttempts($request);

            return $this->sendLoginResponse($request);
        }

        Log::warning('Login failed - invalid credentials', [
            'email' => $request->input('email'),
        ]);

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $credentials = $this->credentials($request);
        
        Log::info('Attempting login with credentials', [
            'email' => $credentials['email'],
            'remember' => $request->boolean('remember'),
        ]);

        $attempt = $this->guard()->attempt(
            $credentials, $request->boolean('remember')
        );

        Log::info('Auth attempt result', ['success' => $attempt]);

        return $attempt;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        Log::info('Sending login response - before session operations', [
            'intended' => $this->redirectPath(),
            'authenticated_user' => Auth::id(),
            'auth_check' => Auth::check(),
            'session_id_before' => session()->getId(),
        ]);

        // Force session save before regeneration
        session()->save();
        
        // Regenerate session ID for security
        $request->session()->regenerate();
        
        // Ensure authentication persists after regeneration
        Auth::login($this->guard()->user(), $request->boolean('remember'));
        
        // Force session save again
        session()->save();

        Log::info('Sending login response - after session operations', [
            'authenticated_user' => Auth::id(),
            'auth_check' => Auth::check(),
            'session_id_after' => session()->getId(),
            'session_data' => session()->all(),
        ]);

        $this->clearLoginAttempts($request);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 204)
                    : redirect()->intended($this->redirectPath());
    }
}
