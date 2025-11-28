<?php

namespace App\Guards;

use Illuminate\Auth\SessionGuard;

class NoRegenerateSessionGuard extends SessionGuard
{
    /**
     * Log a user into the application without session regeneration.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  bool  $remember
     * @return void
     */
    public function login($user, $remember = false)
    {
        $this->updateSession($user->getAuthIdentifier());

        if ($remember) {
            $this->ensureRememberTokenIsSet($user);
            $this->queueRecallerCookie($user);
        }

        $this->fireLoginEvent($user, $remember);

        $this->setUser($user);
        
        // DO NOT REGENERATE SESSION - this is the key fix
        // Original Laravel code: $this->session->regenerate(true);
    }
}