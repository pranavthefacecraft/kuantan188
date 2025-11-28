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
        $currentSessionId = session()->getId();
        
        Log::info('Login attempt started', [
            'email' => $request->input('email'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $currentSessionId,
            'csrf_token' => $request->input('_token'),
            'all_input' => $request->except('password'),
        ]);

        $this->validateLogin($request);
        Log::info('Login validation passed');

        // Throttle check
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            Log::warning('Too many login attempts', ['email' => $request->input('email')]);
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        // Authentication with proper Laravel methods
        $credentials = $this->credentials($request);
        
        \DB::beginTransaction();
        try {
            $user = \App\Models\User::where('email', $credentials['email'])->first();
            
            if ($user && \Hash::check($credentials['password'], $user->password)) {
                // Ensure user is fresh and persisted
                $user->refresh();
                $user->updated_at = now();
                $user->save();
                
                // Commit before authentication
                \DB::commit();
                
                Log::info('User verified, attempting proper Laravel login', [
                    'user_id' => $user->id,
                    'session_id_before' => session()->getId(),
                ]);
                
                // Use Laravel's proper loginUsingId method
                // This sets both session data AND guard's current user
                Auth::loginUsingId($user->id, $request->boolean('remember'));
                
                // DO NOT regenerate session - this is what was causing the issue!
                // $request->session()->regenerate(); // Comment this out
                
                Log::info('Laravel loginUsingId completed', [
                    'user_id' => Auth::id(),
                    'auth_check' => Auth::check(),
                    'session_id_after' => session()->getId(),
                    'session_stayed_same' => session()->getId() === $currentSessionId,
                    'session_data' => session()->all(),
                ]);
                
                $this->clearLoginAttempts($request);
                
                return redirect()->intended($this->redirectPath());
            } else {
                \DB::rollback();
            }
        } catch (\Throwable $e) {
            \DB::rollback();
            Log::error('Login process failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
            ]);
        }

        Log::warning('Login failed - invalid credentials', ['email' => $request->input('email')]);
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
            'current_session_id' => session()->getId(),
        ]);

        // Store current session ID before attempt
        $currentSessionId = session()->getId();
        
        $attempt = $this->guard()->attempt(
            $credentials, $request->boolean('remember')
        );

        Log::info('Auth attempt result', [
            'success' => $attempt,
            'session_id_before' => $currentSessionId,
            'session_id_after' => session()->getId(),
            'session_changed' => $currentSessionId !== session()->getId(),
        ]);

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

        // DON'T regenerate session - this is causing the issue!
        // Just clear login attempts and redirect with existing session
        $this->clearLoginAttempts($request);

        Log::info('Sending login response - after session operations', [
            'authenticated_user' => Auth::id(),
            'auth_check' => Auth::check(),
            'session_id_after' => session()->getId(),
            'session_data' => session()->all(),
        ]);

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
                    ? new JsonResponse([], 204)
                    : redirect()->intended($this->redirectPath());
    }
}
