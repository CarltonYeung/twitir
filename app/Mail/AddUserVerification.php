<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Requests;
use App\User;

class AddUserVerification extends Mailable
{
    use Queueable, SerializesModels;

    private $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user.adduserverification')->with([
            'username' => $this->user->username,
            'verify_link' => request()->getHttpHost().'/verify?email='.$this->user->email.'&key='.$this->user->verification_key,
            'verification_key' => $this->user->verification_key,
        ]);
    }
}
