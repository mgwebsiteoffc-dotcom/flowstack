<?php

namespace App\Services;

use App\Mail\AgencyMail;
use App\Models\ClientPortalUser;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

/**
 * Central notification dispatcher: in-app database notifications (bell icon)
 * plus queued emails with per-user, per-type opt-out preferences stored in the
 * settings table under email_notify_{type}.
 */
class NotificationService
{
 /**
 * Send an in-app notification to a single team user.
 *
 * @param array<string, mixed> $data
 */
 public function notifyUser(User $user, string $title, string $body, string $routeName, array $data = [], string $priority = 'normal'): void
 {
 $user->notify(new InAppNotification($title, $body, $routeName, $data, $priority));
 }

 /**
 * Send an in-app notification to every user with one of the given roles.
 *
 * @param array<int, string> $roles
 * @param array<string, mixed> $data
 */
 public function notifyRole(Tenant $tenant, array $roles, string $title, string $body, string $routeName, array $data = [], string $priority = 'normal'): void
 {
 $users = User::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)
 ->whereIn('role', $roles)
 ->where('is_active', true)
 ->get();

 foreach ($users as $user) {
 $this->notifyUser($user, $title, $body, $routeName, $data, $priority);
 }
 }

 /**
 * Queue an email to a team user, respecting their notification preferences.
 *
 * @param array<string, mixed> $data
 */
 public function sendEmail(User $user, string $subject, string $message, string $type = 'general', array $data = [], ?Mailable $custom = null): void
 {
 if (! $this->userWantsEmail($user, $type)) {
 return;
 }

 $mail = $custom ?? new AgencyMail($subject, $message, $data);

 Mail::to($user->email, $user->name)->queue($mail);
 }

 /**
 * Queue an email to every active user with one of the given roles.
 *
 * @param array<int, string> $roles
 */
 public function emailRole(Tenant $tenant, array $roles, string $subject, string $message, string $type = 'general'): void
 {
 $users = User::withoutGlobalScopes()
 ->where('tenant_id', $tenant->id)
 ->whereIn('role', $roles)
 ->where('is_active', true)
 ->get();

 foreach ($users as $user) {
 $this->sendEmail($user, $subject, $message, $type);
 }
 }

 /**
 * Email a client portal user (client contacts do not have per-user prefs).
 *
 * @param array<string, mixed> $data
 */
 public function sendEmailToPortalUser(ClientPortalUser $user, string $subject, string $message, array $data = []): void
 {
 Mail::to($user->email, $user->name)->queue(new AgencyMail($subject, $message, $data));
 }

 /**
 * Email an arbitrary address (used for billing contacts).
 *
 * @param array<string, mixed> $data
 */
 public function sendEmailToAddress(string $email, ?string $name, string $subject, string $message, array $data = []): void
 {
 Mail::to($email, $name)->queue(new AgencyMail($subject, $message, $data));
 }

 /**
 * Per-user email preference: setting email_notify_{type} = 0 disables it.
 * Missing preference = enabled (default true).
 */
 public function userWantsEmail(User $user, string $type): bool
 {
 $value = Setting::withoutGlobalScopes()
 ->where('tenant_id', $user->tenant_id)
 ->where('key', 'email_notify_'.$type)
 ->value('value');

 return $value === null || (bool) $value;
 }
}
