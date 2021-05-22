<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        // VerifyEmail::toMailUsing(function (User $user, string $verificationUrl) {
        //     return (new MailMessage)
        //         ->subject(Lang::get('Verify Email Address'))
        //         ->line(Lang::get('Please click the button below to verify your email address.'))
        //         ->action(Lang::get('Verify Email Address'), $verificationUrl)
        //         ->line(Lang::get('If you did not create an account, no further action is required.'));
        // });
        //
    }
}
