<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InAppNotification extends Notification
{
 use Queueable;

 public function __construct(
 public string $title,
 public string $body,
 public string $routeName,
 public array $data = [],
 public string $priority = 'normal'
 ) {
 }

 /**
 * Database channel only (in-app bell). Emails go through NotificationService.
 */
 public function via(object $notifiable): array
 {
 return ['database'];
 }

 public function toDatabase(object $notifiable): array
 {
 return [
 'title' => $this->title,
 'body' => $this->body,
 'route' => $this->routeName,
 'data' => $this->data,
 'priority' => $this->priority,
 ];
 }

 public function toArray(object $notifiable): array
 {
 return $this->toDatabase($notifiable);
 }
}
