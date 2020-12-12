<?php

namespace Modules\KPI\Notifications;

use App\Helper\Reply;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\KPI\Entities\Employee;
use Modules\KPI\Entities\Infraction;

class InfractionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Infraction $infraction, $headmessage, $bodymessage, $deleted = false)
    {
        $this->infraction = $infraction;
        $this->headmessage = $headmessage;
        $this->bodymessage = $bodymessage;
        $this->deleted = $deleted;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $user = Employee::find($notifiable->id);
        if ($user->hasRole('admin')) {
            $url = route('admin.kpi.infractions.index');
        } else {
            $url = route('admin.kpi.infractions.index');
        }
        $this->creator = Employee::find($this->infraction->created_by)->name;

        return (new MailMessage)
        ->subject($this->headmessage . ' #' . $this->infraction->id . ' - ' . config('app.name'))
        ->from(config('mail.from.address'), auth()->user()->name . ' via ' . config('app.name'))
        ->markdown('kpi::mail.infraction',
            [
                'infraction' => $this->infraction,
                'url' => $url,
                'headmessage' => $this->headmessage,
                'bodymessage' => $this->bodymessage,
                'creator' => $this->creator,
                'deleted' => $this->deleted
            ]
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
