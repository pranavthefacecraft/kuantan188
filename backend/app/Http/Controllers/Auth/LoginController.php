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
        $this->validateLogin($request);

        // Throttle check
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
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
                
                // Use Laravel's proper loginUsingId method
                // This sets both session data AND guard's current user
                Auth::loginUsingId($user->id, $request->boolean('remember'));
                
                // DO NOT regenerate session to avoid cookie conflicts
                
                $this->clearLoginAttempts($request);
                
                return redirect()->intended($this->redirectPath());
            } else {
                \DB::rollback();
            }
        } catch (\Throwable $e) {
            \DB::rollback();
            Log::error('Login authentication failed', [
                'error' => $e->getMessage(),
                'email' => $request->input('email'),
            ]);
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
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
