<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CustomLoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function username()
    {
        return 'school_id';
    }

    /**
     * Show the application's login form.
     *
     * @return View
     */
    public function showLoginForm(): View
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

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }

            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Log the request method for debugging
        Log::info('Logout request received', [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip()
        ]);

        // Perform logout
        $this->guard()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // If this was called from the loggedOut hook
        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        // Handle JSON requests
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Logged out successfully'], 200);
        }

        // Redirect to login with success message
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => $this->redirectPath(),
                'message' => 'Login successful'
            ]);
        }

        if ($response = $this->authenticated($request, $this->guard()->user())) {
            return $response;
        }

        return $request->wantsJson()
            ? response()->json(['success' => true, 'redirect' => $this->redirectPath()])
            : redirect()->intended($this->redirectPath());
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $credentials = $this->credentials($request);

        // Check if user exists
        $user = \App\Models\User::where('school_id', $credentials['school_id'])->first();

        if (!$user) {
            $message = 'No account found with this School ID.';
            $field = 'school_id';
        } else {
            // User exists but password is wrong
            $message = 'The password you entered is incorrect.';
            $field = 'password';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => [
                    $field => [$message]
                ]
            ], 422);
        }

        throw ValidationException::withMessages([
            $field => [$message],
        ]);
    }
}
